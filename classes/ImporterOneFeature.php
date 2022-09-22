<?php
class ImporterOneFeature {

    public function __construct() {

    }

    private static function retrieveTemplateByName($idCategory) {
        if (empty($idCategory)) {
            throw new Exception("Specificare idCategory per cercare il Template");//TODO CT Ho cambiato il messaggio dell'exception
        }

        $result = null;
        $sql = "SELECT * FROM " . _DB_PREFIX_ . "layered_filter WHERE NAME LIKE '" . pSQL("modello-filtri-cat-" . $idCategory) . "%'";
        $result = DB::getInstance()->executeS($sql);
        if (empty($result)) {
            return null;
        }

        return $result[0];
    }

    /**
     * Questo metodo si aspetta come parametro un array con queste chiavi:
     * "name" => il nome del template che si andrà a creare o aggiornare
     * "filters" => i filtri serializzati
     * "n_categories" => il numero delle categorie a cui va applicato il template
     */
    public static function saveTemplate($templateData, $module) {
        try {
            $result = self::retrieveTemplateByName($templateData['filters']['categories'][0]);
            $templateData['idLayeredFilter'] = isset($result['id_layered_filter']) ? $result['id_layered_filter'] : 0; // aggiungo l'id per aggiornare il template trovato
            if (empty($result)) {
                if (self::insertNewTemplate($templateData) <= 0) {
                    $module->io6_write_log("Non è stato possibile effettuare l'inserimento del template " . $templateData['name'], IO6_LOG_ERROR);
                    return false;
                }
                $module->io6_write_log("Inserimento del template " . $templateData['name'] . " completato.", IO6_LOG_INFO);
            } else {
                // recupero l'array dei filtri
                $updateFilters = $templateData['filters'];
                // faccio il merge dell'array dei filtri presenti dentro
                $updateFilters = array_merge(Tools::unSerialize($result['filters']), $updateFilters);
                // prima di passare l'array dei filtri modificato inserisco l
                self::buildLayeredCategories($templateData['filters'], count($updateFilters));
                $templateData['filters'] = $updateFilters;
    
                if (!self::updateTemplate($templateData)) { //TODO CT Il metodo updateTemplate NON ha una return e siccome 0 in php equivale a false, entra sempre qui dentro restituedo a sua volta false
                    $module->io6_write_log("Non è stato possibile aggiornare il template " . $templateData['name'], IO6_LOG_ERROR);
                    return false;
                }
                $module->io6_write_log("Aggiornamento del template " . $templateData['name'] . " completato.", IO6_LOG_INFO);
            }
    
            // //TODO: da valutare se usare il metodo originale
            // self::buildLayeredCategories($templateData);
    
            return true;
        } catch (Exception $e) {
            $module->io6_write_log("Errore metodo saveTemplate: " . $e->getMessage(), IO6_LOG_ERROR);
            return false;
        }
    }


    private static function insertNewTemplate($data) {
        try {
            if (empty($data)) {
                throw new Exception("I dati da inserire dentro la tabella per la creazione del template non possono essere tutti nulli.");
            }
    
            // caratteristiche di default
            $data['filters']["layered_selection_subcategories"] = array("filter_type" => 0, "filter_show_limit" => 0);
            $data['filters']["layered_selection_stock"] = array("filter_type" => 0, "filter_show_limit" => 0);
            $data['filters']["layered_selection_manufacturer"] = array("filter_type" => 0, "filter_show_limit" => 0);
            $data['filters']["layered_selection_condition"] = array("filter_type" => 0, "filter_show_limit" => 0);
            $data['filters']["layered_selection_weight_slider"] = array("filter_type" => 0, "filter_show_limit" => 0);
            $data['filters']["layered_selection_price_slider"] = array("filter_type" => 0, "filter_show_limit" => 0);
            
            
            $data['date_add'] = date('Y-m-d H:i:s');
            $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'layered_filter ' .
            '(name, filters, n_categories, date_add, id_layered_filter) ' .
            'VALUES (' .
            '"' . pSQL($data['name']) . '", ' .
            '"' . pSQL(serialize($data['filters'])) . '", ' .
            '' . (int) $data['n_categories'] . ', ' .
            '"' . pSQL($data['date_add']) . '",' .
            '' . (int) $data["idLayeredFilter"] . ')';
            DB::getInstance()->execute($sql);

            $id_layered_filter = (int) DB::getInstance()->Insert_ID();

            self::buildLayeredCategories($data['filters']);
    
            $ret_val = (int) DB::getInstance()->Insert_ID();

            foreach ($data['filters']["shop_list"] as $key => $shop) {
                Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'layered_filter_shop(id_layered_filter, id_shop) VALUES(' . $id_layered_filter . ',' . $shop . ')');
            }
            
    
            return $ret_val;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Se return è true allora ha effettuato l'aggiornamento altrimenti entra nell'eccezione
     */
    private static function updateTemplate($data) {
        try {
            if (empty($data)) {
                throw new Exception("I dati da inserire dentro la tabella per la creazione del template non possono essere tutti nulli.");
            }
    
            $sql = 'UPDATE ' . _DB_PREFIX_ . 'layered_filter ' .
            'SET name = "' . pSQL($data['name']) . '", ' .
            'filters = "' . pSQL(serialize($data['filters'])) . '", ' .
            'n_categories = ' . (int) $data['n_categories'] . ' ' .
            'WHERE id_layered_filter = ' . $data['idLayeredFilter'];
            return DB::getInstance()->execute($sql);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function buildLayeredCategories($data, $position = 0) {
        try {
            $idCategory = $data['categories'][0];
            $idShop = $data['shop_list'][0];
            foreach ($data as $key => $value) {
                if (substr($key, 0, 17) == 'layered_selection') {
                    $idValue = null;
                    $position++;
                    $filterShowLimit = $value['filter_show_limit'];
                    $filterType = $value['filter_type'];
    
                    if ($key == 'layered_selection_stock') {
                        $type = "quantity";
                    } elseif ($key == 'layered_selection_subcategories') {
                        $type = "category";
                    } elseif ($key == 'layered_selection_condition') {
                        $type = "condition";
                    } elseif ($key == 'layered_selection_weight_slider') {
                        $type = "weight";
                    } elseif ($key == 'layered_selection_price_slider') {
                        $type = "price";
                    } elseif ($key == 'layered_selection_manufacturer') {
                        $type = "manufacturer";
                    // } elseif (substr($key, 0, 21) == 'layered_selection_ag_') {
                    //     $type = "id_attribute_group";
                    //     $idValue = (int) str_replace('str', '', $key);
                    } elseif (substr($key, 0, 23) == 'layered_selection_feat_') {
                        $type = "id_feature";
                        $idValue = (int) str_replace('layered_selection_feat_', '', $key);
                        // il controllo se la caratteristica è già presente nella tabella vale solo per i filtri importati da importerOne
                        $sql = 'SELECT COUNT(*) as tot FROM ' . _DB_PREFIX_ . 'layered_category WHERE id_category = ' . (int) $idCategory . ' AND id_shop = ' . (int) $idShop . ' AND id_value = ' . (int) $idValue;
                        $result = DB::getInstance()->executeS($sql);
        
                        if (!empty($result) && $result[0]['tot'] > 0) {
                            continue;
                        }
                    }
    
                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'layered_category (id_category, id_shop, id_value, type, position, filter_show_limit, filter_type) VALUES ' .
                        '('. (int) $idCategory . ','. (int) $idShop . ','. (int) $idValue . ',\''. pSQL($type) . '\','. (int) $position . ','. (int) $filterShowLimit . ','. (int) $filterType . ')';
                    if (!DB::getInstance()->execute($sql)) {
                        return false;
                    }
                }
            }
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
<?php

/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2020 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

define('IO6_LOG_INFO', 'INFO');
define('IO6_LOG_WARNING', 'WARNING');
define('IO6_LOG_ERROR', 'ERROR');

define('IO6_LOG_DIRPATH', _PS_UPLOAD_DIR_ . 'io6-logs' . DIRECTORY_SEPARATOR);
define('IO6_IMAGES_DIRPATH', _PS_UPLOAD_DIR_ . 'io6-images' . DIRECTORY_SEPARATOR);


define('IO6_PHP_MIN', '7.4.13');
define('IO6_PHP_MAX', '7.4.33');
define('IO6_MAX_EXECUTION_TIME', 300);
define('IO6_MEMORY_LIMIT', 512);
define('IO6_PS_VERSION_MIN', '1.7.5.0');
define('IO6_PS_VERSION_MAX', '1.7.8.7');


require_once('core/src/classes/IO6ConnectEngine.class.php');

//use DoctrineExtensions\Query\Mysql\Now;
use PrestaShopBundle\Entity\Repository\TabRepository;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

//TODO Implementare modo di gestire anche gli errori fatali, in modo da restituire sempre una risposta gestita alla chiamata ajax (o allo scheduler)
//TODO Attenzione che registrando in questo modo, si intercettano TUTTI gli errori anche non di questo modulo; studiare bene...
// register_shutdown_function( "fatal_handler" );
// function fatal_handler() {
// $errfile = "unknown file";
// $errstr  = "shutdown";
// $errno   = E_CORE_ERROR;
// $errline = 0;

// $error = error_get_last();

// if($error !== NULL) {
// $errno   = $error["type"];
// $errfile = $error["file"];
// $errline = $error["line"];
// $errstr  = $error["message"];

// //error_mail(format_error( $errno, $errstr, $errfile, $errline));
// echo("$errno, $errstr, $errfile, $errline");
// }
// }
class Ps_Connect_Io6 extends Module  implements WidgetInterface
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'ps_connect_io6';
        $this->tab = 'quick_bulk_update';
        $this->version = '1.1.1';
        $this->author = 'Imprimis';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('ImporterONE Cloud Connector');
        $this->description = $this->l('Connette il tuo negozio con ImporterONE Cloud');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.7.5.0', 'max' => '1.7.8.7'); //_PS_VERSION_

        if (!file_exists(IO6_LOG_DIRPATH))
            mkdir(IO6_LOG_DIRPATH, 0775, true);
        if (!file_exists(IO6_IMAGES_DIRPATH))
            mkdir(IO6_IMAGES_DIRPATH, 0775, true);
    }

    public $tabs = array(array(
        'name' => array(
            'it' => 'ImporterONE Cloud Connector',
            'en' => 'ImporterONE Cloud Connector',
        ),
        'class_name' => 'AdminPsConnectIo6',
        'parent_class_name' => 'AdminCatalog',
    ));


    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        //Configuration::updateValue('IMPORTERONE6CONNECT_LIVE_MODE', false);

        include(dirname(__FILE__) . '/sql/install.php');

        Configuration::updateValue('IMPORTERONE6CONNECT_PAGESIZE', 25);
        Configuration::updateValue('IMPORTERONE6CONNECT_IMAGELIMIT', 0);
        Configuration::updateValue('IMPORTERONE6CONNECT_FIELD_REFERENCE', 'io6_sku_partNumber');
        Configuration::updateValue('IMPORTERONE6CONNECT_MANAGE_IMAGES', 1);
        Configuration::updateValue('IMPORTERONE6CONNECT_DELAYED_DOWNLOADS_IMAGES', 1);
        Configuration::updateValue('IMPORTERONE6CONNECT_MANAGE_FEATURES', 1);
        Configuration::updateValue('IMPORTERONE6CONNECT_MANAGE_TITLE', 1);
        Configuration::updateValue('IMPORTERONE6CONNECT_MANAGE_PRICES', 1);
        Configuration::updateValue('IMPORTERONE6CONNECT_MANAGE_DESCRIPTION', 1);
        Configuration::updateValue('IMPORTERONE6CONNECT_MANAGE_CATEGORIES', 1);
        Configuration::updateValue('IMPORTERONE6CONNECT_MANAGE_SHORTDESCRIPTION', 1);
        Configuration::updateValue('IMPORTERONE6CONNECT_MANAGE_HTMLFEATURES', 1);
        Configuration::updateValue('IMPORTERONE6CONNECT_TEMPLATE_HTMLFEATURES', 0);
        Configuration::updateValue('IMPORTERONE6CONNECT_MANAGE_TAX_RULE_DEFAULT', 0);
        Configuration::updateValue('IMPORTERONE6CONNECT_TAX_RULE_DEFAULT', 0);
        Configuration::updateValue('IMPORTERONE6CONNECT_CONCAT_HTMLFEATURES', 1);
        Configuration::updateValue('IMPORTERONE6CONNECT_EXCLUDE_NOIMAGE', 1);
        Configuration::updateValue('IMPORTERONE6CONNECT_MANAGE_FACETEDSEARCH_MODELS', 0);
        Configuration::updateValue('IMPORTERONE6CONNECT_EXCLUDE_AVAILLESSTHAN', 0);
        Configuration::updateValue('IMPORTERONE6CONNECT_EXCLUDE_AVAILTYPE', 0);

        return parent::install() &&
            //$this->registerHook('header') &&
            $this->registerHook('displayFooterProduct') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionProductUpdate') &&
            $this->registerHook('displayAdminProductsExtra');
        //TODO Aggiungere Hook actionCategoryDelete per pulire tabella ps_importerone6connect_category e anche per altre entità

    }


    public function uninstall()
    {
        // Configuration::deleteByName('IMPORTERONE6CONNECT_LIVE_MODE');

        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Legge le configurazioni del modulo salvate nella tabella Configuration di Prestashop.
     * Ritorna un array con ogni configurazione come chiave=>valore necessaria per IO6ConnectConfiguration 
     */
    private function getIO6ConnectConfiguration()
    {

        $configuration = [];
        $configuration['apitoken'] = Configuration::get('IMPORTERONE6CONNECT_API_TOKEN');
        $configuration['apiendpoint'] = Configuration::get('IMPORTERONE6CONNECT_API_ENDPOINT');
        $configuration['catalog'] = Configuration::get('IMPORTERONE6CONNECT_CATALOG');
        //$configuration['languagecode'] : ''; //CHIEDERE
        //$configuration['tempfolder'] : ''; //CHIEDERE

        $configuration['price_list'] =  Configuration::get('IMPORTERONE6CONNECT_PRICE_LIST');
        $configuration['page_size'] = Configuration::get('IMPORTERONE6CONNECT_PAGESIZE');
        $configuration['image_limit'] = Configuration::get('IMPORTERONE6CONNECT_IMAGELIMIT');


        $configuration['select_sku_field'] = Configuration::get('IMPORTERONE6CONNECT_FIELD_REFERENCE');

        //$configuration['select_brand_field'] : 'io6_product_brand';
        //$configuration['select_ean_field'] : 'io6_eancode';
        //$configuration['select_partnumber_field'] : 'io6_partnumber';

        $configuration['manage_images'] = Configuration::get('IMPORTERONE6CONNECT_MANAGE_IMAGES');
        $configuration['delayed_downloads_images'] = Configuration::get('IMPORTERONE6CONNECT_DELAYED_DOWNLOADS_IMAGES');
        $configuration['manage_features'] = Configuration::get('IMPORTERONE6CONNECT_MANAGE_FEATURES');
        $configuration['manage_title'] = Configuration::get('IMPORTERONE6CONNECT_MANAGE_TITLE');
        $configuration['manage_prices'] = Configuration::get('IMPORTERONE6CONNECT_MANAGE_PRICES');
        $configuration['manage_content'] = Configuration::get('IMPORTERONE6CONNECT_MANAGE_DESCRIPTION');
        $configuration['manage_categories'] = Configuration::get('IMPORTERONE6CONNECT_MANAGE_CATEGORIES');
        $configuration['manage_excerpt'] = Configuration::get('IMPORTERONE6CONNECT_MANAGE_SHORTDESCRIPTION');
        $configuration['manage_tax_rule'] = Configuration::get('IMPORTERONE6CONNECT_MANAGE_TAX_RULE_DEFAULT');
        $configuration['manage_features_html'] = Configuration::get('IMPORTERONE6CONNECT_MANAGE_HTMLFEATURES');
        $configuration['concat_features_html'] = Configuration::get('IMPORTERONE6CONNECT_CONCAT_HTMLFEATURES');
        $configuration['features_html_template'] = Configuration::get('IMPORTERONE6CONNECT_TEMPLATE_HTMLFEATURES');

        $configuration['exclude_noimage'] = Configuration::get('IMPORTERONE6CONNECT_EXCLUDE_NOIMAGE');
        $configuration['exclude_avail_lessthan'] = Configuration::get('IMPORTERONE6CONNECT_EXCLUDE_AVAILLESSTHAN');
        $configuration['exclude_avail_type'] = Configuration::get('IMPORTERONE6CONNECT_EXCLUDE_AVAILTYPE');

        return $configuration;
    }

    public function io6TestApi(){
        $io6_configuration = new IO6ConnectConfiguration($this->getIO6ConnectConfiguration());
        $io6Engine = new IO6ConnectEngine($io6_configuration);

        try {
            $results = $io6Engine->TestApi(Tools::getValue('ep', ''), Tools::getValue('t', ''));

        } catch (Exception $ex) {
            $results = null;
        }

        echo isset($results) ? json_encode($results) : '{}';
        die();
    }

    /**
     * 
     */
    public function io6Sync()
    {
        $io6_configuration = new IO6ConnectConfiguration($this->getIO6ConnectConfiguration());
        $io6Engine = new IO6ConnectEngine($io6_configuration);

        $currentPage = intval(Tools::getValue('page', 1));
        $syncFast = intval(Tools::getValue('fast', 0));
        $syncResume = intval(Tools::getValue('resume', 0));

        if ($currentPage == 1) {
            $this->cleanImporterone6connectTable();

            if (!$syncFast) {
                $this->syncCategories($io6Engine);
                Category::regenerateEntireNtree();

                $this->syncBrands($io6Engine);
            }

            $this->syncSuppliers($io6Engine);
        }

        
        
        $results = $this->syncProducts($io6Engine, $io6_configuration, $currentPage, $syncFast, $syncResume);



        //status_header(200);

        echo isset($results) ? json_encode($results) : '{}';
        die();
    }


    public function cleanImporterone6connectTable()
    {
        //Pulisco eventuali righe orfane senza corrispondenza nella tabella delle Catagorie Prestashop
        $sql = 'DELETE ' . _DB_PREFIX_ . 'importerone6connect_categories.* FROM `' . _DB_PREFIX_ . 'importerone6connect_categories` 
                LEFT JOIN `' . _DB_PREFIX_ . 'category` ON `' . _DB_PREFIX_ . 'importerone6connect_categories`.`id_category` = `' . _DB_PREFIX_ . 'category`.`id_category`
                WHERE `' . _DB_PREFIX_ . 'category`.`id_category` IS NULL';
        Db::getInstance()->execute($sql);

        //Pulisco eventuali righe orfane senza corrispondenza nella tabella dei Marchi Prestashop
        $sql = 'DELETE ' . _DB_PREFIX_ . 'importerone6connect_manufacturers.* FROM `' . _DB_PREFIX_ . 'importerone6connect_manufacturers` 
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` ON `' . _DB_PREFIX_ . 'importerone6connect_manufacturers`.`id_manufacturer` = `' . _DB_PREFIX_ . 'manufacturer`.`id_manufacturer`
                WHERE `' . _DB_PREFIX_ . 'manufacturer`.`id_manufacturer` IS NULL';
        Db::getInstance()->execute($sql);

        //Pulisco eventuali righe orfane senza corrispondenza nella tabella dei Supplier Prestashop
        $sql = 'DELETE ' . _DB_PREFIX_ . 'importerone6connect_suppliers.* FROM `' . _DB_PREFIX_ . 'importerone6connect_suppliers` 
                LEFT JOIN `' . _DB_PREFIX_ . 'supplier` ON `' . _DB_PREFIX_ . 'importerone6connect_suppliers`.`id_supplier` = `' . _DB_PREFIX_ . 'supplier`.`id_supplier`
                WHERE `' . _DB_PREFIX_ . 'supplier`.`id_supplier` IS NULL';
        Db::getInstance()->execute($sql);

        //Pulisco eventuali righe orfane senza corrispondenza nella tabella dei Prodotti Prestashop
        $sql = 'DELETE ' . _DB_PREFIX_ . 'importerone6connect_products.* FROM `' . _DB_PREFIX_ . 'importerone6connect_products` 
                LEFT JOIN `' . _DB_PREFIX_ . 'product` ON `' . _DB_PREFIX_ . 'importerone6connect_products`.`id_product` = `' . _DB_PREFIX_ . 'product`.`id_product`
                WHERE `' . _DB_PREFIX_ . 'product`.`id_product` IS NULL';
        Db::getInstance()->execute($sql);

        //Pulisco eventuali righe orfane senza corrispondenza nella tabella delle immagini Prestashop
        $sql = 'DELETE ' . _DB_PREFIX_ . 'importerone6connect_images.* FROM `' . _DB_PREFIX_ . 'importerone6connect_images`
                LEFT JOIN `' . _DB_PREFIX_ . 'image` ON `' . _DB_PREFIX_ . 'image`.id_product = `' . _DB_PREFIX_ . 'importerone6connect_images`.id_product AND `' . _DB_PREFIX_ . 'image`.id_image = `' . _DB_PREFIX_ . 'importerone6connect_images`.id_image
                WHERE `' . _DB_PREFIX_ . 'image`.id_product IS NULL; ';
        Db::getInstance()->execute($sql);


        //Pulisco eventuali righe orfane senza corrispondenza nella tabella dei PrezziSpecifici Prestashop
        $sql = 'DELETE ' . _DB_PREFIX_ . 'importerone6connect_specific_price.* FROM `' . _DB_PREFIX_ . 'importerone6connect_specific_price`
        LEFT JOIN `' . _DB_PREFIX_ . 'specific_price` ON `' . _DB_PREFIX_ . 'specific_price`.id_product = `' . _DB_PREFIX_ . 'importerone6connect_specific_price`.id_product AND `' . _DB_PREFIX_ . 'specific_price`.id_specific_price = `' . _DB_PREFIX_ . 'importerone6connect_specific_price`.id_specific_price
        WHERE `' . _DB_PREFIX_ . 'specific_price`.id_product IS NULL; ';
        Db::getInstance()->execute($sql);
    }


    protected $ps_categories_cache = array();
    protected $ps_brands_cache = array();
    protected $ps_suppliers_cache = array();
    protected $ps_products_cache = array();
    protected $shopRootCategory = 0;

    protected static function createMultiLangField($field)
    {
        $res = [];
        foreach (Language::getIDs(false) as $id_lang) {
            $res[$id_lang] = $field;
        }

        return $res;
    }

    /**
     * Interroga la tabella ps_importerone6connect_category e restituisce array io6_category_code => id_category
     */
    private function preloadImporteroneCategories()
    {
        $retVal = [];
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'importerone6connect_categories';

        $results = DB::getInstance()->executeS($sql);
        foreach ($results as $row) {
            $retVal[$row['io6_category_code']] = $row['id_category'];
        }
        return $retVal;
    }


    private function preloadImporteroneManufacturers()
    {
        $retVal = [];
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'importerone6connect_manufacturers';

        $results = DB::getInstance()->executeS($sql);
        foreach ($results as $row) {
            $retVal[$row['io6_brand_code']] = $row['id_manufacturer'];
        }
        return $retVal;
    }

    private function preloadImporteroneSuppliers()
    {
        $retVal = [];
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'importerone6connect_suppliers';

        $results = DB::getInstance()->executeS($sql);
        foreach ($results as $row) {
            $retVal[$row['io6_id_supplier']] = $row['id_supplier'];
        }
        return $retVal;
    }

    private function preloadImporteroneProducts()
    {
        //TODO 20210508 Valutare se in caso di cataloghi da diverse migliaia se può degradare le prestazioni (viene ricaricata ad ogni paginazione)
        $retVal = [];
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'importerone6connect_products WHERE IFNULL(io6_id_product, 0) > 0';

        $results = DB::getInstance()->executeS($sql);
        foreach ($results as $row) {
            $retVal[$row['io6_id_product']] = $row['id_product'];
        }
        return $retVal;
    }

    private function psCategorySave($name, $id_parent, $io6_category_code, $id_category = 0)
    {

        if (empty($id_category))
            $ps_category = new Category();
        else {
            $ps_category = new Category($id_category);
            if (!Validate::isLoadedObject($ps_category)) {
                $this->io6_write_log("Categoria Prestashop non caricata. id_category non valido: " . $id_category, IO6_LOG_INFO);
                return false;
            }
            $sql = "SELECT id_group FROM " . _DB_PREFIX_ . "category_group WHERE id_category = " . $ps_category->id;
            $result = Db::getInstance()->executeS($sql);
            $category_groups = [];
            foreach ($result as $group) {
                $category_groups[] = $group['id_group'];
            }
            $ps_category->groupBox = $category_groups;
        }

        $ps_category->id_parent = $id_parent;
        $ps_category->doNotRegenerateNTree = true;/* No automatic nTree regeneration for import */
        $ps_category->name = self::createMultiLangField($name);
        $ps_category->link_rewrite = Tools::link_rewrite($name);
        $ps_category->link_rewrite = self::createMultiLangField($ps_category->link_rewrite);



        //$ps_category->force_id = true;
        $ps_category->id_shop_default = (int)Context::getContext()->shop->id;

        if (empty($id_category)) {
            $ps_category->date_add = date('Y-m-d H:i:s');
            $res = $ps_category->add();
        } else
            $res = $ps_category->update();

        if ($res) {
            $sql = "REPLACE INTO " . _DB_PREFIX_ . "importerone6connect_categories (id_category, io6_category_code)
                VALUES (" . $ps_category->id . ",'" . pSQL($io6_category_code) . "')";
            DB::getInstance()->execute($sql);

            $this->ps_categories_cache[$io6_category_code] = $ps_category->id;
        }

        return $res ? $ps_category : false;
    }

    private function psManufacturerSave($name, $io6_brand_code, $logo, $id_manufacturer = 0)
    {

        if (empty($id_manufacturer))
            $ps_manufacturer = new Manufacturer();
        else {
            $ps_manufacturer = new Manufacturer($id_manufacturer);
            if (!Validate::isLoadedObject($ps_manufacturer)) {
                $this->io6_write_log("Manufacturer Prestashop non caricato. id_manufacturer non valido: " . $id_manufacturer, IO6_LOG_INFO);
                return false;
            }
        }

        $ps_manufacturer->name = $name;
        $ps_manufacturer->active = 1;

        if (empty($id_manufacturer)) {
            $ps_manufacturer->date_add = date('Y-m-d H:i:s');
            $res = $ps_manufacturer->add();
        } else
            $res = $ps_manufacturer->update();

        if ($res) {
            $sql = "REPLACE INTO " . _DB_PREFIX_ . "importerone6connect_manufacturers (id_manufacturer, io6_brand_code)
                VALUES (" . $ps_manufacturer->id . ",'" . pSQL($io6_brand_code) . "')";
            DB::getInstance()->execute($sql);

            $this->ps_brands_cache[$io6_brand_code] = $ps_manufacturer->id;
        }


        $sql = "SELECT logo FROM " . _DB_PREFIX_ . "importerone6connect_manufacturers WHERE id_manufacturer = " . (int)$ps_manufacturer->id;
        $old_logo = DB::getInstance()->getValue($sql);
        if (!empty($logo) && $old_logo != $logo) {
            if (@$this->generateImgIntoCms($ps_manufacturer->id, null, $logo, 'manufacturers')) {
                $sql = "UPDATE  " . _DB_PREFIX_ . "importerone6connect_manufacturers SET logo='" . pSQL($logo) . "' WHERE id_manufacturer = " . (int)$ps_manufacturer->id;
                DB::getInstance()->execute($sql);
            } else {
                $this->io6_write_log("Logo Manufacturer non salvato. logo: " . $logo, IO6_LOG_WARNING);
            }
        }

        return $res ? $ps_manufacturer : false;
    }

    private function psSupplierSave($name, $io6_id_supplier, $id_supplier = 0)
    {

        if (empty($id_supplier))
            $ps_supplier = new Supplier();
        else {
            $ps_supplier = new Supplier($id_supplier);
            if (!Validate::isLoadedObject($ps_supplier)) {
                $this->io6_write_log("Supplier Prestashop non caricato. id_supplier non valido: " . $id_supplier, IO6_LOG_INFO);
                return false;
            }
        }
        $ps_supplier->name = $name;
        $ps_supplier->active = 1;

        if (empty($id_supplier)) {
            $ps_supplier->date_add = date('Y-m-d H:i:s');
            $res = $ps_supplier->add();
        } else
            $res = $ps_supplier->update();

        if ($res) {
            $sql = "REPLACE INTO " . _DB_PREFIX_ . "importerone6connect_suppliers (id_supplier, io6_id_supplier)
                VALUES (" . (int)$ps_supplier->id . "," . (int)$io6_id_supplier . ")";
            DB::getInstance()->execute($sql);

            $this->ps_suppliers_cache[$io6_id_supplier] = $ps_supplier->id;
        }

        return $res ? $ps_supplier : false;
    }

    public function syncCategories(IO6ConnectEngine $io6Engine,  $categories = null)
    {
        $default_language = Configuration::get('PS_LANG_DEFAULT');

        if (!isset($categories)) {
            $categories = $io6Engine->GetIO6Categories();
            //Precarico in array tutto l'elenco delle categorie associate ad ImpoterONE
            $this->ps_categories_cache = $this->preloadImporteroneCategories();

            $this->shopRootCategory = Context::getContext()->shop->getCategory();
        }

        foreach ($categories as $category) {

            $category->name = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
                return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
            }, $category->name);
            $category->name =  substr(preg_replace('/[<>;=#{}]/', '', $category->name), 0, 128);


            //Cerca Categoria Parent tramite Codice IO6
            $ps_category_id_parent = 0;

            if (empty($category->parentCode)) //Se Vuoto o 0, imposto nella root
                $ps_category_id_parent = $this->shopRootCategory;
            else if (isset($this->ps_categories_cache[$category->parentCode])) {
                $ps_category_id_parent = (int)$this->ps_categories_cache[$category->parentCode];
            } else {
                //TODO: EM20210319 => almeno fare log. capire se saltare la categoria.
            }

            //Cerca Categoria tramite Codice IO6
            $ps_categoryId = !empty($this->ps_categories_cache[$category->code]) ? $this->ps_categories_cache[$category->code] : 0;

            if (empty($ps_categoryId)) { //Cerco per nome categoria
                $categoryFounded = Category::searchByNameAndParentCategoryId($default_language, $category->name, $ps_category_id_parent);
                if (!empty($categoryFounded) && $categoryFounded['id_category']) {
                    $ps_categoryId = $categoryFounded['id_category'];
                }
            }


            if ($ps_categoryId) {
                $ps_category = $this->psCategorySave($category->name, $ps_category_id_parent, $category->code, $ps_categoryId);
            } else {
                $ps_category = $this->psCategorySave($category->name, $ps_category_id_parent, $category->code);
                // if($ps_category !== false) {
                //     $ps_categoryId = $ps_category->id;                   
                // }
            }

            if (count($category->subCategories) > 0)
                $this->syncCategories($io6Engine, $category->subCategories);
        }
    }

    public function syncBrands(IO6ConnectEngine $io6Engine)
    {
        $brands = $io6Engine->GetIO6Brands();
        //Precarico in array tutto l'elenco dei brand associati ad ImpoterONE
        $this->ps_brands_cache = $this->preloadImporteroneManufacturers();

        foreach ($brands as $brand) {
            $brand->name = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
                return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
            }, $brand->name);
            $brand->name =  substr(preg_replace('/[<>;=#{}]/', '', $brand->name), 0, 64);


            //Cerca Categoria tramite Codice IO6
            $ps_brandId = !empty($this->ps_brands_cache[$brand->code]) ? $this->ps_brands_cache[$brand->code] : 0;


            if (empty($ps_brandId))
                $ps_brandId = Manufacturer::getIdByName($brand->name);

            $ps_brand = null;
            if ($ps_brandId)
                $ps_brand = $this->psManufacturerSave($brand->name, $brand->code, $brand->logo, $ps_brandId);
            else {
                $ps_brand = $this->psManufacturerSave($brand->name, $brand->code, $brand->logo);
                // if ($ps_brand !== false) {
                //     $ps_brandId = $ps_brand->id;
                // }
            }
        }
    }

    public function syncSuppliers(IO6ConnectEngine $io6Engine)
    {
        $suppliers = $io6Engine->GetIO6Suppliers();
        //Precarico in array tutto l'elenco dei suppliers associati ad ImpoterONE
        $this->ps_suppliers_cache = $this->preloadImporteroneSuppliers();

        foreach ($suppliers as $supplier) {
            $supplier->name = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
                return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
            }, $supplier->name);
            $supplier->name =  substr(preg_replace('/[<>;=#{}]/', '', $supplier->name), 0, 64);

            //Cerca Categoria tramite Codice IO6
            $ps_supplierId = !empty($this->ps_suppliers_cache[$supplier->id]) ? $this->ps_suppliers_cache[$supplier->id] : 0;

            if (empty($ps_supplierId))
                $ps_supplierId = Supplier::getIdByName($supplier->name);

            $ps_supplier = null;
            if ($ps_supplierId)
                $ps_supplier = $this->psSupplierSave($supplier->name, $supplier->id, $ps_supplierId);
            else {
                $ps_supplier = $this->psSupplierSave($supplier->name, $supplier->id);
            }
        }
    }

    function syncProducts(IO6ConnectEngine $io6Engine, IO6ConnectConfiguration $io6_configuration, $currentPage = 1, $fastSync = 0, $syncResume = 0)
    {
        if (Shop::isFeatureActive())
            Context::getContext()->shop->setContext(Shop::CONTEXT_ALL);
        else
            Shop::setContext(Shop::CONTEXT_SHOP, (int) Context::getContext()->shop->id);


        if ($currentPage == 1) {
            //Resettare lastsync per individuare successivamente solo i prodotti sincronizzati in questa sincro 
            Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'importerone6connect_products`
                                SET `' . _DB_PREFIX_ . 'importerone6connect_products`.`lastsync` = NULL;');
        }


        $default_language = Configuration::get('PS_LANG_DEFAULT');

        $skuField = $io6_configuration->selectedSkuField;
        $skuProp = str_replace('io6_sku_', '', $skuField);
        $eanField = 'ean13'; //$io6_configuration->selectedEanField;
        $partNumberField = 'mpn'; //$io6_configuration->selectedPartNumberField;
        $brandField = 'id_manufacturer'; // $io6_configuration->selectedBrandField;


        $io6_results = $io6Engine->GetIO6Products($currentPage);
        $this->ps_products_cache = $this->preloadImporteroneProducts();

        //TODO: EM20210330 => se il prodotto è obsoleto e nn esiste nemmeno lo creo, stessa cosa se con tutti i flag filtro risulta disattivato

        $syncResults = array();
        $syncResults['pages'] = $io6_results['pages'];
        $syncResults['elementsFounds'] = $io6_results['elementsFounds'];
        $syncResults['products'] = array();
        foreach ($io6_results['products'] as $io6product) {
            //Carico flag con valori di default del modulo
            $update_title = $io6_configuration->manageTitle;
            $update_content = $io6_configuration->manageContent;
            $update_excerpt = $io6_configuration->manageExcerpt;
            $update_categories = $io6_configuration->manageCategories;
            $update_prices = $io6_configuration->managePrices;
            $update_images = $io6_configuration->manageImages;
            $delayedDownloadsImages = $io6_configuration->delayedDownloadsImages;
            $update_features = $io6_configuration->manageFeatures;
            $update_features_html = $io6_configuration->manageFeaturesHTML;
            $concat_features_html = $io6_configuration->concatFeaturesHTML; //TODO CT 20211016 Verificare, se non viene usato rimuovere anche da configuration
            //$excludeNoImage = $io6_configuration->excludeNoImage;
            $update_tax_rule = $io6_configuration->manageTaxRule;

            $manage_facetedsearch_models = Configuration::get('IMPORTERONE6CONNECT_MANAGE_FACETEDSEARCH_MODELS') == 1 && !$fastSync;

            $retProduct = array('io6_id' => $io6product->id, 'ean' => $io6product->ean, 'partnumber' => $io6product->partNumber);

            $activeState = $io6product->isActive && $io6product->statusCode != 99;



            try {
                if (!$activeState) {
                    $this->io6_write_log("Prodotto escluso perchè non attivo in ImporterONE. IO6 Product Id: " . $io6product->id, IO6_LOG_INFO);
                    throw new Exception("Prodotto escluso perchè non attivo in ImporterONE");
                    // $retProduct['status_message'] = "Prodotto non attivo";
                    // array_push($syncResults['products'], $retProduct);
                    // //TODO CT 20210521 Non va bene fare la continue, andrebbe aggiornata almeno la tabella ps_importerone6connect_products con lo sync_status e sync_message
                    // continue;
                }

                $ps_product_id = isset($this->ps_products_cache[$io6product->id]) ? $this->ps_products_cache[$io6product->id] : 0;
                $ps_brand_id =    isset($this->ps_brands_cache[$io6product->brandCode]) ? $this->ps_brands_cache[$io6product->brandCode] : 0;
                $ps_supplier_id =    isset($this->ps_suppliers_cache[$io6product->supplierId]) ? $this->ps_suppliers_cache[$io6product->supplierId] : 0;
                $ps_category_id = isset($this->ps_categories_cache[$io6product->categoryCode]) ? $this->ps_categories_cache[$io6product->categoryCode] : 0;

                $ps_brand = null;
                $ps_category = null;


                if ($ps_product_id == 0) {
                    if ($fastSync) {
                        throw new Exception("Prodotto non aggiornabile con procedura FAST perchè non esistente in Prestashop o non abbinato ad ImporterONE.");
                        //$this->io6_write_log("Prodotto non aggiornabile con procedura FAST perchè non esistente in Prestashop o non associato ad ImporterOne.", IO6_LOG_INFO);
                        //continue;
                    }
                    //TODO CT 20210508 Se faccio la preloadProducts fuori, non ho bisogno di questa query di ricerca singola=> Capire cosa è più prestante, dipende anche dalla paginazione.
                    // $sql = "SELECT post_id FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key='io6_product_id' AND $wpdb->postmeta.meta_value=$product->id";
                    // $results = $wpdb->get_results($wpdb->prepare($sql));
                    // if (isset($results) && count($results) > 0) {
                    //     $ps_product_id = $results[0]->post_id;
                    // }

                    if ($ps_product_id == 0 && !empty($io6product->$skuProp)) {
												
                        $sql = "SELECT id_product FROM " . _DB_PREFIX_ . "product p 
																WHERE p.reference = '" . pSQL($io6product->$skuProp) . "'";

                        if ($skuProp == 'partNumber')
														$sql .= " AND id_manufacturer = " . (int)$ps_brand_id;

                        $results = DB::getInstance()->getValue($sql);
                        if ($results !== false) {
                            $ps_product_id = (int)$results;
                        }
                    }

                    if ($ps_product_id == 0 && !empty($io6product->ean)) {
                        $sql = "SELECT id_product FROM " . _DB_PREFIX_ . "product WHERE " . pSQL($eanField) . " = '" . pSQL($io6product->ean) . "'";
                        $results = DB::getInstance()->getValue($sql);
                        if ($results !== false) {
                            $ps_product_id = (int)$results;
                        }
                    }
										
										
                    if (version_compare(_PS_VERSION_, '1.7.7', '>=')) {
                        if ($ps_product_id == 0 && !empty($io6product->partNumber)) {
                            $sql = "SELECT id_product FROM " . _DB_PREFIX_ . "product WHERE " . pSQL($partNumberField) . " = '" . pSQL($io6product->partNumber) . "' AND id_manufacturer = " . (int)$ps_brand_id;
                            $results = DB::getInstance()->getValue($sql);
                            if ($results !== false) {
                                $ps_product_id = (int)$results;
                            }
                        }
                    }
                }


                //TODO: EM20210330 => verifica isactive, statuscode e altri filtri. Se condizione è false disattiva il prodotto.
                //SE nn esiste nn lo crea, se esiste nn lo aggiorna e mette qta = 0

                if (!$fastSync) {
                    if ($ps_brand_id == 0) { //CT 20210510 Non facendo sempre la preloadManufactures, necessito di fare le singole query per ogni prodotto
                        $sql = 'SELECT id_manufacturer FROM ' . _DB_PREFIX_ . 'importerone6connect_manufacturers WHERE io6_brand_code=\'' . pSQL($io6product->brandCode) . '\'';
                        $results = DB::getInstance()->getValue($sql);
                        if ($results !== false) {
                            $ps_brand_id = (int)$results;
                            $this->ps_brands_cache[$io6product->brandCode] = $ps_brand_id;
                        }
                    }

                    if ($ps_supplier_id == 0) { //CT 20210510 Non facendo sempre la preloadSuppliers, necessito di fare le singole query per ogni prodotto
                        $sql = 'SELECT id_supplier FROM ' . _DB_PREFIX_ . 'importerone6connect_suppliers WHERE io6_id_supplier=' . (int)$io6product->supplierId;
                        $results = DB::getInstance()->getValue($sql);
                        if ($results !== false) {
                            $ps_supplier_id = (int)$results;
                            $this->ps_suppliers_cache[$io6product->supplierId] = $ps_supplier_id;
                        }
                    }

                    if ($ps_category_id == 0) { //CT 20210510 Non facendo sempre la preloadCategories, necessito di fare le singole query per ogni prodotto
                        $sql = 'SELECT id_category FROM ' . _DB_PREFIX_ . 'importerone6connect_categories WHERE io6_category_code=\'' . pSQL($io6product->categoryCode) . '\'';
                        $results = DB::getInstance()->getValue($sql);
                        if ($results !== false) {
                            $ps_category_id = (int)$results;
                            $this->ps_categories_cache[$io6product->categoryCode] = $ps_category_id;
                        }
                    }

                    if ($ps_brand_id == 0 || $ps_category_id == 0) {
                        throw new Exception("No brand [$io6product->brandCode] or category [$io6product->categoryCode] found for product $ps_product_id");
                    }
                }


                //TODO CT 20210510 - La seguente procedura di salvataggio del prodotto potrebbe essere spostata su un metodo apposito come fatto per psManufacturerSave e psCategorySave
                $isNewProduct = false;
                $has_cms_images = false;
                if ($ps_product_id) {
                    $io6ConfigProduct = $this->getImporteroneConnectProduct($ps_product_id);

                    if ($syncResume && $io6ConfigProduct['is_synced']) {
                        $retProduct['activeState'] = $activeState;
                        $retProduct['status'] = 'OK';
                        $retProduct['status_message'] = "Prodotto ignorato perchè già importato nella precedente sincronizzazione";
                        array_push($syncResults['products'], $retProduct);
                        continue;
                    }


                    if (!isset($io6ConfigProduct['exclude_sync']) || intval($io6ConfigProduct['exclude_sync']) == 1) {
                        throw new Exception("Product $ps_product_id is not managed by " . $this->displayName);
                    }

                    $update_title = isset($io6ConfigProduct['manage_title']) && intval($io6ConfigProduct['manage_title']) != 2 ? intval($io6ConfigProduct['manage_title']) : $update_title;
                    $update_content = isset($io6ConfigProduct['manage_description']) && intval($io6ConfigProduct['manage_description']) != 2 ? intval($io6ConfigProduct['manage_description']) : $update_content;
                    $update_excerpt = isset($io6ConfigProduct['manage_shortdescription']) && intval($io6ConfigProduct['manage_shortdescription']) != 2 ? intval($io6ConfigProduct['manage_shortdescription']) : $update_excerpt;
                    $update_categories = isset($io6ConfigProduct['manage_categories']) && intval($io6ConfigProduct['manage_categories']) != 2 ? intval($io6ConfigProduct['manage_categories']) : $update_categories;
                    $update_prices = isset($io6ConfigProduct['manage_prices']) && intval($io6ConfigProduct['manage_prices']) != 2 ? intval($io6ConfigProduct['manage_prices']) : $update_prices;
                    $update_images = isset($io6ConfigProduct['manage_images']) && intval($io6ConfigProduct['manage_images']) != 2 ? intval($io6ConfigProduct['manage_images']) : $update_images;
                    $update_features = isset($io6ConfigProduct['manage_features']) && intval($io6ConfigProduct['manage_features']) != 2 ? intval($io6ConfigProduct['manage_features']) : $update_features;
                    $manage_facetedsearch_models = $update_features ? $manage_facetedsearch_models : false;
                    $update_features_html = isset($io6ConfigProduct['manage_htmlfeatures']) && intval($io6ConfigProduct['manage_htmlfeatures']) != 2 ? intval($io6ConfigProduct['manage_htmlfeatures']) : $update_features_html;
                    $update_tax_rule = isset($io6ConfigProduct['manage_tax_rule']) && intval($io6ConfigProduct['manage_tax_rule']) != 2 ? intval($io6ConfigProduct['manage_tax_rule']) : $update_tax_rule;

                    $ps_product = new Product($ps_product_id, false);
                    $has_cms_images = (Product::getCover($ps_product_id) !== false);

                    if ($fastSync) {
                        $update_title = false;
                        $update_content = false;
                        $update_excerpt = false;
                        $update_categories = false;
                        $update_images = false;
                        $update_features = false;
                        $update_features_html = false;
                        $manage_facetedsearch_models = false;
                        $update_tax_rule = false;
                    }
                } else {
                    $isNewProduct = true;
                    $update_title = true;
                    $update_content = true;
                    $update_excerpt = true;
                    $update_categories = true;
                    $update_prices = true;
                    $update_images = $io6_configuration->manageImages;
                    $delayedDownloadsImages = $io6_configuration->delayedDownloadsImages;
                    $update_features = $io6_configuration->manageFeatures;
                    $manage_facetedsearch_models = $update_features ? $manage_facetedsearch_models : false;
                    $update_features_html = $io6_configuration->manageFeaturesHTML;
                    $update_tax_rule = $io6_configuration->manageTaxRule;
                    $ps_product = new Product();
                    //$ps_product->id_tax_rules_group = (int)$info['id_tax_rules_group']; //TODO CT20210519 Aggiungere parametro di configurazione per indicare l'aliquota iva di default da utilizzare per i nuovi prodotti

                }

                // if ($excludeNoImage && count($io6product->images) == 0 && !$has_cms_images && !$fastSync) {
                //     $activeState = false;
                //     $this->io6_write_log("Prodotto escluso perchè senza immagini. IO6 Product Id: " . $io6product->id, IO6_LOG_INFO);
                //     throw new Exception("Prodotto escluso perchè senza immagini");
                //     // array_push($syncResults['products'], $retProduct);
                //     // //TODO CT 20210521 Non va bene fare la continue, andrebbe aggiornata almeno la tabella ps_importerone6connect_products con lo sync_status e sync_message
                //     // continue;
                // }

                $ps_product->active = $activeState;

                if (!$fastSync) {
                    $ps_product->reference = $io6product->$skuProp;

                    if (isset($io6product->partNumber) && !empty($io6product->partNumber))
                        $ps_product->$partNumberField =  $io6product->partNumber;

                    if (isset($io6product->ean) && !empty($io6product->ean))
                        $ps_product->$eanField = $io6product->ean;

                    $ps_product->minimal_quantity = $io6product->minLimitQty;

                    $ps_product->$brandField = (int)($ps_brand_id);

                    if (!empty($ps_supplier_id))
                        $ps_product->id_supplier = (int)($ps_supplier_id);

                    $ps_product->weight = $io6product->weight;
                    $ps_product->width = $io6product->width;
                    $ps_product->depth = $io6product->length;
                    $ps_product->height = $io6product->height;
                }
                $categoryChanged = false;
                if ($update_categories && ((int)$ps_product->id_category_default != $ps_category_id)) {
                    //Assegno al prodotto la nuova categoria
                    $ps_product->id_category_default = $ps_category_id;
                    $categoryChanged = true;
                }

                if (($update_title && isset($io6product->title))) {
                    $ps_product->name = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
                        return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
                    }, $io6product->title);
                    $ps_product->name =  substr(preg_replace('/[<>;=#{}]/', '', $ps_product->name), 0, 128);

                    $link_rewrite = Tools::link_rewrite($ps_product->name);
                    if ($link_rewrite == '' || !Validate::isLinkRewrite($link_rewrite))
                        $link_rewrite = 'friendly-url-autogeneration-failed';
                    $ps_product->link_rewrite = self::createMultiLangField($link_rewrite);
                    // if (Shop::isFeatureActive())
                    //     Context::getContext()->shop->setContext(Shop::CONTEXT_ALL);
                    //TODO Verificare se è necessario impostare CONTEXT_ALL per far salvare link_rewrite su tutti gli shop    
                }
                if (($update_content && isset($io6product->fullDescription))) {
                    $ps_product->description = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
                        return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
                    }, $io6product->fullDescription);
                    $ps_product->description = "<div>" . $ps_product->description . "</div>";
                    $ps_product->description =  preg_replace('/\\\\n/', '<br/>', $ps_product->description);
                }

                if (($update_excerpt && isset($io6product->shortDescription))) {
                    $ps_product->description_short = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
                        return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
                    }, $io6product->shortDescription);

                    $ps_product->description_short = "<div>" . $ps_product->description_short . "</div>";
                    $ps_product->description_short =  substr(preg_replace('/\\\\n/', '<br/>', $ps_product->description_short), 0, 400);
                }

                if ($update_tax_rule) {
                    $ps_product->id_tax_rules_group = Configuration::get('IMPORTERONE6CONNECT_TAX_RULE_DEFAULT');
                }


                $ps_product->ecotax = $io6product->raeeAmount;

                //if($io6product->arrivalDate != "0001-01-01T00:00:00")
                if (Validate::isDateFormat($io6product->arrivalDate))
                    $ps_product->available_date = $io6product->arrivalDate;
                //$officialPrice;			NN c'è un campo su Prestashop	

                if ($update_prices) {
                    $ps_product->on_sale = ($io6product->sellingCustomPrice > 0 && $io6product->sellingCustomPrice < $io6product->sellingPrice) ? 1 : 0; //TODO CT 20210511 Forse va aggiunta anche la verifica sulle date della promo
                    $ps_product->price = $io6product->sellingPrice + $io6product->siaeAmount;

                    $ps_product->wholesale_price = ($io6product->promoPrice > 0 && $io6product->promoPrice < $io6product->dealerPrice) ? $io6product->promoPrice + $io6product->siaeAmount : $io6product->dealerPrice + $io6product->siaeAmount;
                }

                $validateFields = $ps_product->validateFields(false, true);
                $validateFieldsLang = $ps_product->validateFieldsLang(false, true);

                $htmlfeatures = '';
                if ($update_features_html) {
                    $htmlfeatures = $io6product->featuresHtml;

                    // if($concat_features_html) {
                    //     $tmp_description = $ps_product->description;

                    //     //cerco se sono già presenti le caratteristiche html nella descrizione
                    //     $fStart = strrpos($tmp_description, '<!--begin featureshtml importerone-->');
                    //     if (!$fStart === false) {
                    //         //sostituisco vecchie caratteristiche con le nuove nella stessa posizione
                    //         $tmpDesc = substr($tmp_description, 0, $fStart);
                    //         $tmpDesc .= $htmlfeatures;

                    //         $fEnd = strrpos($tmp_description, '<!--end featureshtml importerone-->');
                    //         if (!$fEnd === false)
                    //             $tmpDesc .= substr($tmp_description, $fEnd + 35);

                    //         $tmp_description = $tmpDesc;
                    //     } else {
                    //         $tmp_description .= '<br/><br/>' . $htmlfeatures;
                    //     }
                    //     $this->io6_write_log("Concatena caratteristiche html...", IO6_LOG_WARNING);

                    //     $ps_product->description = $tmp_description;                    
                    // }
                }

                if ($validateFields !== true or $validateFieldsLang !== true) {
                    throw new Exception("Product fields value not valid: " . $validateFields . "-" . $validateFieldsLang);
                }


                if ($isNewProduct) {
                    $res = @$ps_product->add();
                } else {
                    $res = @$ps_product->update();
                }
                if (!$res) {
                    throw new Exception("Product Save not completed");
                }

                if ($update_categories && $categoryChanged) {
                    //Assegna prodotto a tutto albero delle categorie
                    $ps_category = new Category($ps_category_id); //TODO: 20210511 CT Per ottimizzare si potrebbe salvare le categorie già istanziate in un dictionary per evitare ogni volta questa query.
                    $tmp_id_categories = [];
                    if (is_array($parentsCats = $ps_category->getParentsCategories()) && count($parentsCats) > 0) {
                        foreach ($parentsCats as $a_p)
                            $tmp_id_categories[] = $a_p['id_category'];
                    }
                    $ps_product->updateCategories($tmp_id_categories);
                }

                if (!empty($ps_supplier_id)) {
                    if (!$isNewProduct) {
                        //Rimuovo vecchi supplier associati
                        $product_supplier_del = new ProductSupplier();
                        $product_supplier_del->id_product = $ps_product->id;
                        $product_supplier_del->id_product_attribute = 0;
                        $product_supplier_del->delete();
                    }
                    $ps_product->addSupplierReference($ps_supplier_id, 0, $io6product->code, ($io6product->promoPrice > 0 && $io6product->promoPrice < $io6product->dealerPrice) ? $io6product->promoPrice + $io6product->siaeAmount : $io6product->dealerPrice + $io6product->siaeAmount);
                }

                if ($update_prices) { //Prezzi specifici, prezzi custom
                    $specificPricesNotImporterONE = [];
                    if (!$isNewProduct) {
                        //azzera eventuali prezzi speciale precedentemente impostati da IO6
                        $specificPricesByImporterONE = $this->getSpecificPricesByImporterONE($ps_product->id);
                        foreach ($specificPricesByImporterONE as $id_specific_price) {
                            $specific_price = new SpecificPrice($id_specific_price);
                            $specific_price->delete();
                        }

                        $specificPricesNotImporterONE = $this->getSpecificPricesCmsAll($ps_product->id); //CT Ho già rimosso quelli i IO6; $this->getSpecificPricesNotImporterONE($ps_product->id);
                    }

                    if ($io6product->sellingCustomPrice > 0 && $io6product->sellingCustomPrice < $io6product->sellingPrice) { //TODO CT 20210517 Va aggiunta anche la verifica sulle date della promo
                        $sellingPrice = $io6product->sellingPrice + $io6product->siaeAmount;
                        $sellingCustomPrice = $io6product->sellingCustomPrice + $io6product->siaeAmount;

                        $sellingCustomPriceUntil = '0000-00-00 00:00:00';
                        // $this->io6_write_log("io6product->sellingCustomPriceUntil: ". $io6product->sellingCustomPriceUntil, IO6_LOG_INFO);
                        if (strtotime($io6product->sellingCustomPriceUntil) !== false) {
                            $sellingCustomPriceUntil = date("Y-m-d H:i:s", strtotime($io6product->sellingCustomPriceUntil));
                            // $this->io6_write_log("sellingCustomPriceUntil: ". $sellingCustomPriceUntil, IO6_LOG_INFO);
                            $sellingCustomPriceUntil = Validate::isDate($sellingCustomPriceUntil)  ? $sellingCustomPriceUntil : '0000-00-00 00:00:00';
                            // $this->io6_write_log("sellingCustomPriceUntil: ". $sellingCustomPriceUntil, IO6_LOG_INFO);
                        } else {
                            $this->io6_write_log("sellingCustomPriceUntil: " . $io6product->sellingCustomPriceUntil . " NON valida per io6product: " . $io6product->id, IO6_LOG_INFO);
                        }

                        $id_shop_list = Shop::getShops(true, null, true);
                        foreach ($id_shop_list as $id_shop) {
                            //$this->echoDebug("updating reduction_price=" . $info['reduction_price'] . " - id product=" . $id . " in shop: " . $id_shop, IO_LEVEL_DEBUG);

                            // $specific_price = SpecificPrice::getSpecificPrice($id, $id_shop, 0, 0, 0, 1, 0, 0, 0, 0);
                            // if (is_array($specific_price) && isset($specific_price['id_specific_price']))
                            // $specific_price = new SpecificPrice((int)$specific_price['id_specific_price']);
                            // else
                            $specific_price = new SpecificPrice();
                            $specific_price->id_product = (int)$ps_product->id;
                            $specific_price->id_specific_price_rule = 0;
                            $specific_price->id_shop = $id_shop;
                            $specific_price->id_currency = 0;
                            $specific_price->id_country = 0;
                            $specific_price->id_group = 0;
                            $specific_price->id_customer = 0;
                            $specific_price->from_quantity = 1;

                            // //Riduzione in percentuale
                            // $specific_price->price = -1;
                            // $specific_price->reduction = round(((isset($info['reduction_price']) AND $info['reduction_price']) ? (1-($info['reduction_price']/$info['price'])): 0), 6);
                            // $specific_price->reduction_type = 'percentage';
                            //Riduzione con sottrazione fissa
                            $specific_price->price = -1;
                            $specific_price->reduction = round(($sellingPrice - $sellingCustomPrice), 6);
                            $specific_price->reduction_type = 'amount';
                            $specific_price->reduction_tax = 0;

                            //$specific_price->from = (isset($info['reduction_from']) && ($info['reduction_from'] != '0001-01-01') && Validate::isDate($info['reduction_from'])) ? $info['reduction_from'] : '0000-00-00 00:00:00';
                            $specific_price->from = '0000-00-00 00:00:00';
                            $specific_price->to = $sellingCustomPriceUntil;

                            if (!$specific_price->add()) {
                                // $this->echoDebug("error updating reduction_price=" . $info['reduction_price'] . " - reduction_from=" . $info['reduction_from'] . " - reduction_to=" . $info['reduction_to'] . " id product=" . $id . " in shop: " . $id_shop, IO_LEVEL_WARNING);
                            }
                        }
                    }

                    //Memorizzo specific_price inviati da ImpoterONE
                    $specificPricesAll = $this->getSpecificPricesCmsAll($ps_product->id); //Recupero nuovamente tutti i prezzi specifici dopo gli eventuali inserimenti fatti da IO6
                    $specificPricesByImporterONENew = array_diff($specificPricesAll, $specificPricesNotImporterONE); //La differenza mi restituisce solo quelli appena inseriti da IO6
                    $this->saveSpecificPricecsByImporterONE($ps_product->id, $specificPricesByImporterONENew);
                }

                //StockAvailable::setQuantity($ps_product->id, 0, (int)$io6product->avail, (int)Context::getContext()->shop->id);
                $id_shop_list = Shop::getContextListShopID();
                foreach ($id_shop_list as $id_shop) {
                    Shop::setContext(Shop::CONTEXT_SHOP, (int) $id_shop); //StockAvailable::setQuantity NECESSITA che il context sia un id_shop singolo
                    StockAvailable::setQuantity($ps_product->id, 0, (int)$io6product->avail, (int)$id_shop);
                }
                if (Shop::isFeatureActive()) //Se necessario ripristino il CONTEXT_ALL
                    Context::getContext()->shop->setContext(Shop::CONTEXT_ALL);
                else
                    Shop::setContext(Shop::CONTEXT_SHOP, (int) Context::getContext()->shop->id);


                if ($update_images) {
                    $io6ConnectGallery = [];

                    if (!$isNewProduct) {
                        $sql = 'SELECT id_product, id_image, image_uri, orderindex, lastupdate FROM ' . _DB_PREFIX_ . 'importerone6connect_images WHERE id_product = ' . (int)$ps_product->id;
                        $io6ConnectGallery = DB::getInstance()->executeS($sql);
                    }
                    foreach ($io6product->images as $key => $io6image) {
                        $to_download = true;

                        if (!$isNewProduct) {

                            $imageRows = array_filter(
                                $io6ConnectGallery,
                                function ($io6ConnectImage) use ($io6image) {
                                    return $io6ConnectImage['image_uri'] == $io6image->imageUri;
                                }
                            );


                            $imageRowExists = !empty($imageRows);

                            if ($imageRowExists) {
                                $ps_image = reset($imageRows);
                                if ($io6image->lastUpdate > $ps_image['lastupdate']) { //immagine ricevuta più recente di quella caricata precedentemente, quindi aggiornare
                                    //Controlla se id_image > 0, altrimenti vuol dire che non non è stata ancora scaricata e inserita in prestashop (postdownload), quindi non è necessario cancellare da prestashop
                                    if ($ps_image['id_image'] > 0) {
                                        //$this->echoDebug("Immagine inviata più recente di quella caricata in Prestashop, quindi rimuovo immagine precedente da Prestashop", IO_LEVEL_DEBUG);
                                        $imageToDel = new Image((int)$ps_image['id_image']);
                                        $resDel = $imageToDel->delete();
                                        //$this->echoDebug("Immagine precedente eliminata - res: " . $resDel, IO_LEVEL_DEBUG);
                                        unset($imageToDel);
                                    }

                                    //Elimino riga da tabella importerone6connect_images
                                    $res = Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'importerone6connect_images` 
                                                                    WHERE  id_product =' . (int)$ps_product->id . ' AND image_uri=\'' . $ps_image['image_uri'] . '\' ; ');
                                } else {
                                    // $this->echoDebug("Non è necessario aggiornare l'immagine", IO_LEVEL_DEBUG);
                                    $to_download = false;
                                    // continue; //TODO CT 20210512 si potrebbe anche fare il continue
                                }
                            }
                        }

                        if ($to_download) {
                            //Verificare parametro se fare il download subito oppure ritardato (realtime dal sito), se ritardato inserire solo la riga nella tabella io_images con id_image = 0
                            if ($delayedDownloadsImages) {
                                //Inserisco riga in tabella io_images con id_image = 0
                                Db::getInstance()->Execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'importerone6connect_images` (id_product, image_uri, id_image, orderindex, lastupdate) 
                                                                VALUES(' . (int)$ps_product->id . ', \'' . pSQL($io6image->imageUri) . '\', 0,' . (int)$io6image->orderIndex . ',' . date('YmdHis', strtotime($io6image->lastUpdate)) . ' ) ; ');
                            } else {
                                //scarica l'immagine

                                $image_data     = file_get_contents($io6image->imageUri); // Get image data								
                                if (!isset($image_data) || $image_data === false) continue;

                                $image_filename = date('YmdHis') . '_' . basename($io6image->imageUri);
                                $image_filepath = IO6_IMAGES_DIRPATH . $image_filename;

                                file_put_contents($image_filepath, $image_data);

                                $cms_image = new Image();
                                $cms_image->id_product = (int)$ps_product->id;
                                $cms_image->position = Image::getHighestPosition((int)$ps_product->id) + 1;
                                $cms_image->cover = ((bool)Image::getCover((int)$ps_product->id)) ? 0 : 1;

                                if ($cms_image->add()) {
                                    // $cms_image->associateTo((int)(Context::getContext()->shop->id)); //Non serve perchè il metodo ->add già associa in automatico a tutti gli shop
                                    if (@$this->generateImgIntoCms((int)$ps_product->id, $cms_image, $image_filepath)) {
                                        // $this->echoDebug("added image=" . $image_filepath . " id product=" . $id_product, IO_LEVEL_DEBUG);
                                        Db::getInstance()->Execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'importerone6connect_images` (id_product, image_uri, id_image, orderindex, lastupdate) 
                                            VALUES(' . (int)$ps_product->id  . ', \'' . pSQL($io6image->imageUri) . '\', ' . (int)$cms_image->id . ',' . (int)$io6image->orderIndex . ',' . date('YmdHis', strtotime($io6image->lastUpdate)) . ' ) ; ');
                                    } else {
                                        $cms_image->delete(); //Rimuovo anche anagrafica immagine
                                        // $this->echoDebug("generateImgIntoCms non riuscita. product_image_url=" . $image_filepath . " id product=" . $id_product, IO_LEVEL_WARNING);
                                        // $this->count_notsaved++;
                                    }
                                } else {
                                    // $this->count_notsaved++;
                                    // $this->echoDebug("errore image add=" . $image_filepath . " id product=" . $id_product, IO_LEVEL_WARNING);
                                }
                                unlink($image_filepath);

                                //usleep(1000000/3);							
                            }
                        }
                    }

                    //pulizia immagini inversa
                    if (!$isNewProduct) {
                        // $sql = 'SELECT id_product, id_image, image_uri, lastupdate FROM '._DB_PREFIX_.'importerone6connect_images WHERE id_product = ' . (int)$ps_product->id;
                        $sql = 'SELECT ' . _DB_PREFIX_ . 'image.id_product, ' . _DB_PREFIX_ . 'image.id_image, ' . _DB_PREFIX_ . 'importerone6connect_images.image_uri as io6ImageUri FROM `' . _DB_PREFIX_ . 'image` 
                            LEFT JOIN  `' . _DB_PREFIX_ . 'importerone6connect_images` ON `' . _DB_PREFIX_ . 'image`.id_product = `' . _DB_PREFIX_ . 'importerone6connect_images`.id_product AND `' . _DB_PREFIX_ . 'image`.id_image = `' . _DB_PREFIX_ . 'importerone6connect_images`.id_image
                            WHERE `' . _DB_PREFIX_ . 'image`.id_product = ' . (int)$ps_product->id;
                        $checkGallery = DB::getInstance()->executeS($sql);

                        foreach ($checkGallery as $checkImage) {
                            $foundImage = !empty($checkImage['io6ImageUri']);

                            $foundImage &= !empty(array_filter(
                                $io6product->images,
                                function ($image) use ($checkImage) {
                                    return $checkImage['io6ImageUri'] == $image->imageUri;
                                }
                            ));

                            if (!$foundImage) {
                                $idImageToDel = (int)$checkImage['id_image'];
                                if ($idImageToDel > 0) {
                                    $imageToDel = new Image($idImageToDel);
                                    $imageToDel->delete();
                                }
                                //Elimino riga da tabella importerone6connect_images
                                $res = Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'importerone6connect_images` 
                                                                WHERE  id_product =' . (int)$checkImage['id_product'] . ' AND image_uri=\'' . pSQL($checkImage['io6ImageUri']) . '\';');
                            }
                        }
                    }

                    //TODO CT 20210515 Fare controllo che ci sia una immagine con flag Cover=1, altrimenti impostarne una.
                }

                if ($update_features) {
                    //     $serialized_attributes = [];
                    //     $position = 0;
                    foreach ($io6product->features as $feature) {
                        if (!$feature->searchable) continue;

                        $attribute_label = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
                            return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
                        }, $feature->name);
                        $attribute_value = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
                            return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
                        }, $feature->value);
                        if (strlen($attribute_value) > 255)
                            $attribute_value = substr($attribute_value, 0, 255);

                        if (empty($attribute_label) || empty($attribute_value) || !Validate::isGenericName($attribute_label) || !Validate::isGenericName($attribute_value)) {
                            //$this->echoDebug("Valori non validi per feature - attribute_label: " . $attribute_label . " - attribute_value: " . $attribute_value, IO_LEVEL_WARNING);
                            continue;
                        }

                        $id_feature = Feature::addFeatureImport($attribute_label);
                        $custom = false; //forzo sempre a false per far abilitare le combo
                        $id_feature_value = (int)FeatureValue::addFeatureValueImport($id_feature, $attribute_value, $ps_product->id, $default_language, $custom);
                        $res = Product::addFeatureProductImport($ps_product->id, $id_feature, $id_feature_value);
                        if (!$res) {
                            throw new Exception("Caratteristica " . $attribute_label . " non salvata per il prodotto " . $ps_product->id);
                        }

                        if ($manage_facetedsearch_models && $ps_product->id_category_default) {

                            $defaultCategory = new Category($ps_product->id_category_default);

                            // INIZIO CREAZIONE TEMPLATE PER LE CARATTERISTICHE
                            require_once('classes/ImporterOneFeature.php');
                            $filtersData = array();
                            // Creazione dei dati da inserire nel template
                            $filtersData['shop_list'] = Shop::getContextListShopID(); // array(Context::getContext()->shop->id);
                            $filtersData['categories'] = array($ps_product->id_category_default);
                            $filtersData['layered_selection_feat_' . $id_feature] = array('filter_type' => 0, 'filter_show_limit' => 0);

                            $templateData = [
                                'name' => pSQL("modello-filtri-cat-" . $ps_product->id_category_default . "-" . $defaultCategory->getName()),
                                'filters' => $filtersData, // andrà poi serializzato
                                'n_categories' => (int) count($filtersData['categories'])
                            ];

                            if (ImporterOneFeature::saveTemplate($templateData, $this)) {
                                $this->io6_write_log("Il template " . $templateData['name'] . " è stato salvato con successo.", IO6_LOG_INFO);
                            } else { //TODO CT Non c'è nessun log nel metodo saveTemplate e quindi se ritorna False non si capisce il motivo
                                $this->io6_write_log("Non è stato possibile salvare il template " . $templateData['name'], IO6_LOG_ERROR);
                            }
                            // FINE CREAZIONE TEMPLATE PER LE CARATTERISTICHE
                        }
                    }
                }


                $retProduct['activeState'] = $activeState;
                $retProduct['status'] = 'OK';
                $retProduct['status_message'] = "Ok";
                $sync_status = 1;
                $sync_message = $retProduct['status_message'];

                if (isset($ps_product->id) && $ps_product->id > 0) {
                    $retProduct['ps_product_id'] = $ps_product->id;

                    //TODO CT 20210510 Il salvataggio del prodotto scatena anche l'hook che già fa la insert, quindi si potrebbe pensare di fare solo l'update o cercare di non far eseguire quell'hook
                    $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'importerone6connect_products` 
                    (`id_product`, `io6_id_product`, `sync_status`, `sync_message`, `lastsync`, `is_synced`, `exclude_sync`, `manage_title`, `manage_shortdescription`, `manage_description`,
                    `manage_categories`, `manage_prices`, `manage_images`, `manage_features`, `manage_htmlfeatures`, `manage_tax_rule`, `htmlfeatures`)
                    VALUES (
                        ' . (int)$ps_product->id . ',
                        ' . (int)$io6product->id . ',
                        ' . (int)$sync_status . ',
                        \'' . pSQL($sync_message) . '\',
                        ' . date('YmdHis') . ',
                        1,0,2,2,2,2,2,2,2,2,2,
                        \'' . pSQL(htmlentities($htmlfeatures)) . '\'
                    ) ON DUPLICATE KEY UPDATE 
                        `io6_id_product` = ' . (int)$io6product->id . ',
                        `sync_status` = ' . (int)$sync_status . ',
                        `sync_message` = \'' . pSQL($sync_message) . '\',
                        `lastsync` = ' . date('YmdHis') . ',
                        `is_synced` = 1
                        ' . ($update_features_html ? ', `htmlfeatures` = \'' . pSQL(htmlentities($htmlfeatures)) . '\' ' : '');
                    //$this->io6_write_log($sql, IO6_LOG_INFO);
                    if (!Db::getInstance()->execute($sql)) {
                        $this->context->controller->errors[] = Tools::displayError('Error: ') . Db::getInstance()->getNumberError() . Db::getInstance()->getMsgError();
                    }
                    $this->ps_products_cache[$io6product->id] = $ps_product->id;
                }
            } catch (Exception $e) {
                $retProduct['status'] = 'KO';
                $retProduct['status_message'] = $e->getMessage();
                $this->io6_write_log($e->getMessage(), IO6_LOG_WARNING); //TODO CT 20210511 Se gli passo $e va in errore di memory limit
            }
            array_push($syncResults['products'], $retProduct);
        }

        //resetCatalog e Rebuild indici se arrivati all'ultima pagina
        if ($currentPage == $io6_results['pages']) {
            //Resettare solo i prodotti gestiti da ImporterONE, ovvero presenti in importerone6connect_products e con flag exclude_sync <> 1    
            Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'product` product
									INNER JOIN `' . _DB_PREFIX_ . 'importerone6connect_products` ioprod ON product.id_product = ioprod.id_product
									SET product.`active` = 0 
									WHERE product.`active` = 1 and ioprod.`exclude_sync` = 0 AND ioprod.io6_id_product > 0 AND ioprod.is_synced = 0;');
            Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'product_shop` p_shop
										INNER JOIN `' . _DB_PREFIX_ . 'product` p ON p_shop.id_product = p.id_product
										SET p_shop.active = p.active 
                                        WHERE p_shop.`active` = 1; ');

            Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'importerone6connect_products` SET is_synced = 0');

            //Rebuild indici
            if (Shop::isFeatureActive())
                Context::getContext()->shop->setContext(Shop::CONTEXT_ALL);
            Search::indexation();
        }

        //$this->io6_write_log("PRODUCT PAGE FINISHED" ,  $fastSync ? "FAST" : "NOT FAST");
        return $syncResults;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {

        /* PER TEST*/
        // include(dirname(__FILE__).'/sql/install.php');

        //$res = $this->preloadImporteroneCategories();
        // $res = $this->psCategorySave('ciaone', 2, '3333');
        //var_dump($res);
        // $ps_category = $this->psCategorySave('CATEGORIA zzzzzzzzzz', 2,'111', 1330);

        // $ps_brand = $this->psManufacturerSave('Brand yyyyyy', '111', 9);
        // $res = $this->preloadImporteroneCategories();
        //$res = $this->preloadImporteroneManufacturers();
        //var_dump($res);


        $output = "";
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitImporterone6connectModule_ApiSettings')) == true) {
            $output .= $this->postProcessApiSettings();
        }
        if (((bool)Tools::isSubmit('submitImporterone6connectModule_GeneralSettings')) == true) {
            $output .= $this->postProcessGeneralSettings();
        }

        if (((bool)Tools::isSubmit('submitImporterone6connectModule_ProductsSettings')) == true) {
            $output .= $this->postProcessProductsSettings();
        }
        if (((bool)Tools::isSubmit('submitImporterone6connectModule_ImportSettings')) == true) {
            $output .= $this->postProcessImportSettings();
        }

        $io6_configuration = new IO6ConnectConfiguration($this->getIO6ConnectConfiguration());
        $io6Engine = new IO6ConnectEngine($io6_configuration);
        $checkApiSettings = $io6Engine->CheckApiConnection();

        if (!$checkApiSettings)
            $output .= $this->displayError($this->l('API SETTINGS non validi o API Endpoint non raggiungibile'));

        $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
            'current_domain' => $_SERVER['HTTP_HOST']
        ));

        $server_requirements = $this->checkServerRequirements();

        if(!$server_requirements['passed']){
            $server_info_output = $this->renderFormServerInfo($server_requirements);
            
            $output .= $this->displayError($server_info_output);
        }

        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        
        $output .= $this->renderFormApiSettings();

        if ($checkApiSettings) {

            $io6Catalogs = $io6Engine->GetIO6Catalogs();

            $io6CatalogsMatchingSelected = array_filter(
                $io6Catalogs,
                function ($e) use ($io6_configuration) {
                    return $e->id == $io6_configuration->catalogId;
                }
            );

            if (count($io6Catalogs) > 0 && empty($io6_configuration->catalogId) || count($io6CatalogsMatchingSelected) == 0)
                array_unshift($io6Catalogs, array('id' => '0', 'name' => 'Selezionare un catalogo'));
            if (count($io6Catalogs) == 0)
                $io6Catalogs = array(array('id' => '0', 'name' => 'Nessun Catalogo disponibile'));

            $io6Pricelists = [];
            if ($io6_configuration->catalogId > 0 && count($io6CatalogsMatchingSelected) > 0) {
                $io6Pricelists = $io6Engine->GetIO6PriceLists();

                $io6PricelistsMatchingSelected = array_filter(
                    $io6Pricelists,
                    function ($e) use ($io6_configuration) {
                        return $e->id == $io6_configuration->priceListId;
                    }
                );
                if (empty($io6_configuration->priceListId) || count($io6PricelistsMatchingSelected) == 0)
                    array_unshift($io6Pricelists, array('id' => '0', 'name' => 'Selezionare un listino'));
                if (count($io6Pricelists) == 0)
                    $io6Pricelists = array(array('id' => '0', 'name' => 'Nessun listino disponibile'));
            }



            /*$io6Catalogs = array( //Per test
                                array('id' => '1', 'name' => 'Catalogo 1'),
                                array('id' => '2', 'name' => 'Catalogo 2')
            );
            $io6Pricelists = array(//Per test
                array('id' => '1', 'name' => 'Listino 1'),
                array('id' => '2', 'name' => 'Listino 2'),
                array('id' => '3', 'name' => 'Listino 3')
            );*/

            $output .= $this->renderFormGeneralSettings($io6Catalogs, $io6Pricelists);

            if (!empty(Configuration::get('IMPORTERONE6CONNECT_CATALOG')) && count($io6CatalogsMatchingSelected) > 0 && count($io6PricelistsMatchingSelected) > 0) {

                $output .= $this->renderFormProductsSettings();

                $output .= $this->renderFormImportSettings();

                $output .= $this->renderFormExecute();
            }
        }


        return $output;
    }

    /* Form di Api Settings Inizio --> */
    protected function renderFormApiSettings()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;

        $helper->table = 'api-settings';
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitImporterone6connectModule_ApiSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValuesApiSettings(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigFormApiSettings()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigFormApiSettings()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('API Settings'),
                    'icon' => 'icon-cogs',
                ),
                'buttons' => array(
                    [
                        'href' => $this->context->link->getModuleLink($this->name, 'actions', ['action' => 'IO6TestAPI']),          // If this is set, the button will be an <a> tag
                        'type' => 'button',         // Button type
                        'id'   => 'io6-test-api',
                        'name' => 'io6-test-api',       // If not defined, this will take the value of "submitOptions{$table}"
                        'title' => 'Test Connessione ImporterONE',      // Button label
                    ],
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Inserisci l\'indirizzo dell\'API EndPoint'),
                        'name' => 'IMPORTERONE6CONNECT_API_ENDPOINT',
                        'label' => $this->l('API EndPoint'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Inserisci il Token generato per l\'API EndPoint'),
                        'name' => 'IMPORTERONE6CONNECT_API_TOKEN',
                        'label' => $this->l('API Token'),
                    ),
                ),
                
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValuesApiSettings()
    {
        return array(
            'IMPORTERONE6CONNECT_API_ENDPOINT' => Configuration::get('IMPORTERONE6CONNECT_API_ENDPOINT'),
            'IMPORTERONE6CONNECT_API_TOKEN' => Configuration::get('IMPORTERONE6CONNECT_API_TOKEN'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcessApiSettings()
    {
        $output = "";
        $result = true;

        $inputValue = Tools::getValue('IMPORTERONE6CONNECT_API_ENDPOINT');
        if (empty($inputValue) || !Validate::isGenericName($inputValue)) {
            $output .= $this->displayError($this->l('\'API EndPoint\' non impostato correttamente'));
            $result &= false;
        }
        $inputValue = Tools::getValue('IMPORTERONE6CONNECT_API_TOKEN');
        if (empty($inputValue) || !Validate::isGenericName($inputValue)) {
            $output .= $this->displayError($this->l('\'API Token\' non impostato correttamente'));
            $result &= false;
        }

        if ($result) {
            $form_values = $this->getConfigFormValuesApiSettings();
            foreach (array_keys($form_values) as $key) {
                $result &= Configuration::updateValue($key, Tools::getValue($key));
            }

            if ($result)
                $output .= $this->displayConfirmation($this->l('Salvataggio Api Settings completato'));
        }

        return $output;
    }

    /* <-- Form di Api Settings Fine  */


    /* Form di General Settings Inizio --> */
    protected function renderFormGeneralSettings(array $io6Catalogs = [], array $io6Pricelists = [])
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitImporterone6connectModule_GeneralSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValuesGeneralSettings(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigFormGeneralSettings($io6Catalogs, $io6Pricelists)));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigFormGeneralSettings(array $io6Catalogs = [], array $io6Pricelists = [])
    {
        $configForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Catalogo'),
                        'name' => 'IMPORTERONE6CONNECT_CATALOG',
                        'desc' => $this->l('Seleziona il Catalogo ImporeterONE da cui importare i prodotti'),
                        'options' => array(
                            'query' => $io6Catalogs,
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        if (!empty(Configuration::get('IMPORTERONE6CONNECT_CATALOG')) && count($io6Pricelists) > 0) {
            $configForm['form']['input'][] = array(
                'type' => 'select',
                'label' => $this->l('Listino'),
                'name' => 'IMPORTERONE6CONNECT_PRICE_LIST',
                'desc' => $this->l('Seleziona il Listino Prezzi ImporeterONE da applicare ai prodotti. Salvare prima la selezione del Catalogo per ottenere l\'elenco dei Listini corrispondenti'),
                'options' => array(
                    'query' => $io6Pricelists,
                    'id' => 'id',
                    'name' => 'name'
                ),
            );
        }

        return $configForm;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValuesGeneralSettings()
    {
        return array(
            'IMPORTERONE6CONNECT_CATALOG' => Configuration::get('IMPORTERONE6CONNECT_CATALOG'),
            'IMPORTERONE6CONNECT_PRICE_LIST' => Configuration::get('IMPORTERONE6CONNECT_PRICE_LIST'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcessGeneralSettings()
    {
        $output = "";
        $result = true;
        $form_values = $this->getConfigFormValuesGeneralSettings();

        foreach (array_keys($form_values) as $key) {
            $result &= Configuration::updateValue($key, Tools::getValue($key));
        }

        if ($result)
            $output .= $this->displayConfirmation($this->l('Salvataggio General Settings completato'));

        return $output;
    }

    /* <-- Form di General Settings Fine  */


    /* Form di Products Settings Inizio --> */
    protected function renderFormProductsSettings()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitImporterone6connectModule_ProductsSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValuesProductsSettings(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigFormProductsSettings()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigFormProductsSettings()
    {
        $noTax[] = ['id_tax_rules_group' => 0, 'name' => "Nessuna tassa"];
        $taxRules = array_merge($noTax, TaxRulesGroup::getTaxRulesGroups());

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Products Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Campo Reference'),
                        'name' => 'IMPORTERONE6CONNECT_FIELD_REFERENCE',
                        'desc' => $this->l('Seleziona quale campo di ImporterONE utilizzare per gestire il Reference in Prestashop. Selezionando Part Number la ricerca dei prodotti già esitenti avverà anche tramite il Marchio del prodotto.'),
                        'options' => array(
                            'query' => array(
                                array('key' => 'io6_sku_partNumber', 'name' => 'Part Number'),
                                array('key' => 'io6_sku_ean', 'name' => 'EAN'),
                                array('key' => 'io6_sku_id', 'name' => 'ID Prodotto ImporterONE')
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Aggiorna i titoli'),
                        'name' => 'IMPORTERONE6CONNECT_MANAGE_TITLE',
                        'is_bool' => true,
                        'desc' => $this->l('Abilita l\'aggiornamento del titolo per prodotti già esistenti. Per i prodotti nuovi verrà sempre salvato.'),
                        'values' => array(
                            array(
                                'id' => 'manage_title_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'manage_title_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Aggiorna i riepiloghi'),
                        'name' => 'IMPORTERONE6CONNECT_MANAGE_SHORTDESCRIPTION',
                        'is_bool' => true,
                        'desc' => $this->l('Abilita l\'aggiornamento del riepilogo(descrizione breve) per prodotti già esistenti. Per i prodotti nuovi verrà sempre salvato.'),
                        'values' => array(
                            array(
                                'id' => 'manage_shortdescription_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'manage_shortdescription_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Aggiorna le descrizioni'),
                        'name' => 'IMPORTERONE6CONNECT_MANAGE_DESCRIPTION',
                        'is_bool' => true,
                        'desc' => $this->l('Abilita l\'aggiornamento della descrizione per prodotti già esistenti. Per i prodotti nuovi verrà sempre salvato.'),
                        'values' => array(
                            array(
                                'id' => 'manage_description_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'manage_description_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Aggiorna associazione Categorie'),
                        'name' => 'IMPORTERONE6CONNECT_MANAGE_CATEGORIES',
                        'is_bool' => true,
                        'desc' => $this->l('Abilita l\'associazione dei prodotti già esistenti con le Categorie. Per i prodotti nuovi verrà sempre salvato. ATTENZIONE: Anche disattivando questa opzione la struttura delle Categorie verrà comunque sincronizzata.'),
                        'values' => array(
                            array(
                                'id' => 'manage_categories_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'manage_categories_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Aggiorna i prezzi'),
                        'name' => 'IMPORTERONE6CONNECT_MANAGE_PRICES',
                        'is_bool' => true,
                        'desc' => $this->l('Abilita l\'aggiornamento del prezzo per prodotti già esistenti. Per i prodotti nuovi verrà sempre salvato.'),
                        'values' => array(
                            array(
                                'id' => 'manage_prices_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'manage_prices_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Salva le immagini'),
                        'name' => 'IMPORTERONE6CONNECT_MANAGE_IMAGES',
                        'is_bool' => true,
                        'desc' => $this->l('Abilita salvataggio delle immagini del prodotto. Impostazione valida sia per prodotti nuovi che già esistenti.'),
                        'values' => array(
                            array(
                                'id' => 'manage_images_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'manage_images_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Scarica le immagini in differita'),
                        'name' => 'IMPORTERONE6CONNECT_DELAYED_DOWNLOADS_IMAGES',
                        'is_bool' => true,
                        'desc' => $this->l('Scarica le immagini in differita. Le immagini non verranno scaricate in Prestashop durante la sincronizzazione ma successivamente alla prima visualizzazione della scheda prodotto. Impostazione valida sia per prodotti nuovi che già esistenti.'),
                        'values' => array(
                            array(
                                'id' => 'delayed_downloads_images_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'delayed_downloads_images_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Salva le caratteristiche'),
                        'name' => 'IMPORTERONE6CONNECT_MANAGE_FEATURES',
                        'is_bool' => true,
                        'desc' => $this->l('Abilita salvataggio delle caratteristriche prodotto (funzioni). Impostazione valida sia per prodotti nuovi che già esistenti.'),
                        'values' => array(
                            array(
                                'id' => 'manage_features_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'manage_features_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Salva Modelli Ricerca per Aspetti'),
                        'name' => 'IMPORTERONE6CONNECT_MANAGE_FACETEDSEARCH_MODELS',
                        'is_bool' => true,
                        'desc' => $this->l('Se si utilizza il modulo Ricerca per Aspetti crea un Modello per ogni Categoria con preselezionate le Caratteristiche indicate come Filtro di ricerca. Può essere utilizzato solo se si attiva l\'opzione "Aggiorna le caratteristiche".  Impostazione valida sia per prodotti nuovi che già esistenti.'),
                        'values' => array(
                            array(
                                'id' => 'facetedsearch_models_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'facetedsearch_models_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Salva le schede HTML'),
                        'name' => 'IMPORTERONE6CONNECT_MANAGE_HTMLFEATURES',
                        'is_bool' => true,
                        'desc' => $this->l('Abilita salvataggio della Scheda Tecnica in formato HTML. Il modulo innesta automaticamente la scheda nella pagina del prodotto nella posizione (hook) "displayProductFooter".  Impostazione valida sia per prodotti nuovi che già esistenti.'),
                        'values' => array(
                            array(
                                'id' => 'manage_htmlfeatures_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'manage_htmlfeatures_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Seleziona template da utilizzare'),
                        'name' => 'IMPORTERONE6CONNECT_TEMPLATE_HTMLFEATURES',
                        'is_bool' => true,
                        'desc' => $this->l('Seleziona il template da utilizzare per la scheda tecnica.'),
                        'options' => array(
                            'query' => array(
                                array('key' => 0, 'name' => 'Default'),
                                array('key' => 1, 'name' => 'Bootstrap 4.x'),
                                array('key' => 2, 'name' => 'Bootstrap 5.x(Plugin MASONRY necessario)')
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Applica aliquota IVA di default'),
                        'name' => 'IMPORTERONE6CONNECT_MANAGE_TAX_RULE_DEFAULT',
                        'is_bool' => true,
                        'desc' => $this->l('Applica l\'aliquota IVA di default ai prodotti che vengono passati senza.  Impostazione valida sia per prodotti nuovi che già esistenti.'),
                        'values' => array(
                            array(
                                'id' => 'manage_tax_rule_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'manage_tax_rule_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Selezionare Aliquota IVA di default'),
                        'name' => 'IMPORTERONE6CONNECT_TAX_RULE_DEFAULT',
                        'is_bool' => true,
                        'desc' => $this->l('Aliquota IVA predefinita per i prodotti.'),
                        'options' => array(
                            'query' => $taxRules,
                            'id' => 'id_tax_rules_group',
                            'name' => 'name'
                        )
                    ),
                    // array(
                    //     'type' => 'switch',
                    //     'label' => $this->l('Accoda Scheda HTML a Descrizione'),
                    //     'name' => 'IMPORTERONE6CONNECT_CONCAT_HTMLFEATURES',
                    //     'is_bool' => true,
                    //     'desc' => $this->l('Accoda Scheda HTML a Descrizione prodotto.'),
                    //     'values' => array(
                    //         array(
                    //             'id' => 'concat_htmlfeatures_on',
                    //             'value' => true,
                    //             'label' => $this->l('Enabled')
                    //         ),
                    //         array(
                    //             'id' => 'concat_htmlfeatures_off',
                    //             'value' => false,
                    //             'label' => $this->l('Disabled')
                    //         )
                    //     ),
                    // ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValuesProductsSettings()
    {
        return array(
            'IMPORTERONE6CONNECT_FIELD_REFERENCE' => Configuration::get('IMPORTERONE6CONNECT_FIELD_REFERENCE'),
            'IMPORTERONE6CONNECT_MANAGE_TITLE' => Configuration::get('IMPORTERONE6CONNECT_MANAGE_TITLE'),
            'IMPORTERONE6CONNECT_MANAGE_DESCRIPTION' => Configuration::get('IMPORTERONE6CONNECT_MANAGE_DESCRIPTION'),
            'IMPORTERONE6CONNECT_MANAGE_SHORTDESCRIPTION' => Configuration::get('IMPORTERONE6CONNECT_MANAGE_SHORTDESCRIPTION'),
            'IMPORTERONE6CONNECT_MANAGE_CATEGORIES' => Configuration::get('IMPORTERONE6CONNECT_MANAGE_CATEGORIES'),
            'IMPORTERONE6CONNECT_MANAGE_PRICES' => Configuration::get('IMPORTERONE6CONNECT_MANAGE_PRICES'),
            'IMPORTERONE6CONNECT_MANAGE_IMAGES' => Configuration::get('IMPORTERONE6CONNECT_MANAGE_IMAGES'),
            'IMPORTERONE6CONNECT_DELAYED_DOWNLOADS_IMAGES' => Configuration::get('IMPORTERONE6CONNECT_DELAYED_DOWNLOADS_IMAGES'),
            'IMPORTERONE6CONNECT_MANAGE_FEATURES' => Configuration::get('IMPORTERONE6CONNECT_MANAGE_FEATURES'),
            'IMPORTERONE6CONNECT_MANAGE_HTMLFEATURES' => Configuration::get('IMPORTERONE6CONNECT_MANAGE_HTMLFEATURES'),
            'IMPORTERONE6CONNECT_CONCAT_HTMLFEATURES' => Configuration::get('IMPORTERONE6CONNECT_CONCAT_HTMLFEATURES'),
            'IMPORTERONE6CONNECT_TEMPLATE_HTMLFEATURES' => Configuration::get('IMPORTERONE6CONNECT_TEMPLATE_HTMLFEATURES'),
            'IMPORTERONE6CONNECT_MANAGE_FACETEDSEARCH_MODELS' => Configuration::get('IMPORTERONE6CONNECT_MANAGE_FACETEDSEARCH_MODELS'),
            'IMPORTERONE6CONNECT_MANAGE_TAX_RULE_DEFAULT' => Configuration::get('IMPORTERONE6CONNECT_MANAGE_TAX_RULE_DEFAULT'),
            'IMPORTERONE6CONNECT_TAX_RULE_DEFAULT' => Configuration::get('IMPORTERONE6CONNECT_TAX_RULE_DEFAULT'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcessProductsSettings()
    {
        $output = "";
        $result = true;
        $form_values = $this->getConfigFormValuesProductsSettings();

        foreach (array_keys($form_values) as $key) {
            if ($key == 'IMPORTERONE6CONNECT_MANAGE_FACETEDSEARCH_MODELS') {
                $value = Tools::getValue('IMPORTERONE6CONNECT_MANAGE_FEATURES') == '1' ? Tools::getValue($key) : 0;
                $result &= Configuration::updateValue($key, $value);
                continue;
            }

            $result &= Configuration::updateValue($key, Tools::getValue($key));
        }

        if ($result)
            $output .= $this->displayConfirmation($this->l('Salvataggio Products Settings completato'));

        return $output;
    }

    /* <-- Form di Products Settings Fine  */

    /* Form di Import Settings Inizio --> */
    protected function renderFormImportSettings()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitImporterone6connectModule_ImportSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValuesImportSettings(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigFormImportSettings()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigFormImportSettings()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Import Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-th"></i>',
                        'desc' => $this->l('Indica quanti prodotti elaborare ad ogni richiesta durante le esecuzioni massive'),
                        'name' => 'IMPORTERONE6CONNECT_PAGESIZE',
                        'label' => $this->l('Numero Prodotti elaborati per ciclo'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-th"></i>',
                        'desc' => $this->l('Indica il numero massimo di immagini da importare per ciascun prodotto: 0 = tutte'),
                        'name' => 'IMPORTERONE6CONNECT_IMAGELIMIT',
                        'label' => $this->l('Numero massimo immagini per prodotto'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Escludi prodotti senza immagini'),
                        'name' => 'IMPORTERONE6CONNECT_EXCLUDE_NOIMAGE',
                        'is_bool' => true,
                        'desc' => $this->l('ImporterONE non invierà prodotti senza immagini e quelli corrispondenti verranno disattivati.'),
                        'values' => array(
                            array(
                                'id' => 'exclude_noimage_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'exclude_noimage_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-th"></i>',
                        'desc' => $this->l('Indicare la disponibilità minima dei prodotti da importare. 0 = Nessuna verifica sulla disponibilità'),
                        'name' => 'IMPORTERONE6CONNECT_EXCLUDE_AVAILLESSTHAN',
                        'label' => $this->l('Disattiva prodotti con disponibilità minore di'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Tipo di disponibilità da considerare'),
                        'name' => 'IMPORTERONE6CONNECT_EXCLUDE_AVAILTYPE',
                        'desc' => $this->l('Indicare il tipo di disponibilità da considerare per la regola precedente.'),
                        'options' => array(
                            'query' => array(
                                array('key' => '0', 'name' =>  $this->l('Disponibilità')),
                                array('key' => '1', 'name' =>  $this->l('In Arrivo')),
                                array('key' => '2', 'name' =>  $this->l('Entrambe')),
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValuesImportSettings()
    {
        return array(
            'IMPORTERONE6CONNECT_PAGESIZE' => Configuration::get('IMPORTERONE6CONNECT_PAGESIZE'),
            'IMPORTERONE6CONNECT_IMAGELIMIT' => Configuration::get('IMPORTERONE6CONNECT_IMAGELIMIT'),
            'IMPORTERONE6CONNECT_EXCLUDE_NOIMAGE' => Configuration::get('IMPORTERONE6CONNECT_EXCLUDE_NOIMAGE'),
            'IMPORTERONE6CONNECT_EXCLUDE_AVAILLESSTHAN' => Configuration::get('IMPORTERONE6CONNECT_EXCLUDE_AVAILLESSTHAN'),
            'IMPORTERONE6CONNECT_EXCLUDE_AVAILTYPE' => Configuration::get('IMPORTERONE6CONNECT_EXCLUDE_AVAILTYPE'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcessImportSettings()
    {
        $output = "";
        $result = true;

        $inputValue = Tools::getValue('IMPORTERONE6CONNECT_PAGESIZE');
        if (!Validate::isUnsignedInt($inputValue)) {
            $output .= $this->displayError($this->l('\'Numero Prodotti elaborati per ciclo\' non impostato correttamente'));
            $result &= false;
        }
        $inputValue = Tools::getValue('IMPORTERONE6CONNECT_IMAGELIMIT');
        if (!Validate::isUnsignedInt($inputValue)) {
            $output .= $this->displayError($this->l('\'Numero massimo immagini per prodotto\' non impostato correttamente'));
            $result &= false;
        }
        $inputValue = Tools::getValue('IMPORTERONE6CONNECT_EXCLUDE_AVAILLESSTHAN');
        if (!Validate::isUnsignedInt($inputValue)) {
            $output .= $this->displayError($this->l('\'Disponibilità minima di un prodotto da importare\' non impostato correttamente'));
            $result &= false;
        }
        if ($result) {
            $form_values = $this->getConfigFormValuesImportSettings();
            foreach (array_keys($form_values) as $key) {
                $result &= Configuration::updateValue($key, Tools::getValue($key));
            }

            if ($result)
                $output .= $this->displayConfirmation($this->l('Salvataggio Import Settings completato'));
        }

        return $output;
    }

    /* <-- Form di Import Settings Fine  */

    public function checkServerRequirements()
    {
        $server_checking = [];
		$server_checking['max_execution_time'] = array(
			'required' => IO6_MAX_EXECUTION_TIME, 
			'current' => intval(ini_get('max_execution_time')), 
			'passed' => (intval(ini_get('max_execution_time')) >= IO6_MAX_EXECUTION_TIME)
		);

		$server_checking['memory_limit'] = array(
			'required' => IO6_MEMORY_LIMIT, 
			'current' => intval(ini_get('memory_limit')), 
			'passed' => (intval(ini_get('memory_limit')) >= IO6_MEMORY_LIMIT)
		);
		$server_checking['php_version'] = array(
			'required' => IO6_PHP_MIN . ' - ' . IO6_PHP_MAX, 
			'current' => phpversion(), 
			'passed' => (version_compare(phpversion(), IO6_PHP_MIN, '>=') && version_compare(phpversion(), IO6_PHP_MAX, '<='))
		);
		$server_checking['ps_version'] = array(
			'required' => IO6_PS_VERSION_MIN . ' - ' . IO6_PS_VERSION_MAX, 
			'current' => _PS_VERSION_, 
			'passed' => (version_compare(_PS_VERSION_, IO6_PS_VERSION_MIN, '>=') && version_compare(_PS_VERSION_, IO6_PS_VERSION_MAX, '<='))
		);
		
        $passed = true;
		foreach($server_checking as $requirement) {
			if($requirement['passed'] == false) {
				$passed = false;
				break;
			}
		}
		$server_checking['passed'] = $passed;
		return $server_checking;
    }


    /**
     * Form Server requirements
     */
    protected function renderFormServerInfo($server_requirements)
    {
        $this->context->smarty->assign(
            array(
                'serverRequirements' => $server_requirements,
            )
        );

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure_server_info.tpl');
        return $output;
    }

    /**
     * Form Execute and Cron
     */
    protected function renderFormExecute()
    {
        $executeUrl = Context::getContext()->link->getModuleLink($this->name, 'actions', array('action' => 'executeSync')); //, 'token' => Tools::getToken(false)
        $cronCommand = "php " . dirname(__FILE__) . '/cron.php "' . $executeUrl . '"';
        $cronCommandFast = "php " . dirname(__FILE__) . '/cron.php "' . $executeUrl . '&fast=1"';

        $cronCommandWarning = $cronCommand . '&accettoAvvisoRequisiti=1"';
        $cronCommandFastWarning = $cronCommandFast . '&accettoAvvisoRequisiti=1"';

        $server_requirements = $this->checkServerRequirements();

        if (Tools::getValue('execute') == '1') {
            $this->io6Sync();
        }

        $this->context->smarty->assign(
            array(
                'executePageSize' => Configuration::get("IMPORTERONE6CONNECT_PAGESIZE"),
                'executeUrl' => $executeUrl,
                'cronCommand' => $cronCommand,
                'cronCommandFast' => $cronCommandFast,
                'cronCommandWarning' => $cronCommandWarning,
                'cronCommandFastWarning' => $cronCommandFastWarning,
                'serverRequirements' => $server_requirements,
                //'cronUrl' => Tools::getHttpHost(true). "/modules/icecool/cron.php?icecoolCron=1" //"?token=".Tools::hash("icecool/cron")
                //'cronUrl' => _PS_BASE_URL_ . "/modules/icecool/cron.php?icecoolCron=1" //"?token=".Tools::hash("icecool/cron")
            )
        );

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure_actions.tpl');
        return $output;
    }


    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/ps_connect_io6.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
            // $this->context->controller->registerStylesheet(
            //     $this->name,
            //     'modules/'.$this->name.'/views/css/back.css',
            //     [
            //       'media' => 'all',
            //       'priority' => 200,
            //     ]
            // );

        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    // public function hookHeader()
    // {
    //     $this->context->controller->addJS($this->_path.'/views/js/front.js');
    //     $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    // }

    public function hookActionFrontControllerSetMedia($params)
    {
        // Only on product page
        if ('product' === $this->context->controller->php_self) {
            $this->context->controller->registerStylesheet(
                'module-' . $this->name . '-productpage-style',
                'modules/' . $this->name . '/views/css/productpage.css',
                [
                    'media' => 'all',
                    'priority' => 200,
                ]
            );
        }
    }


    /**
     * TODO CT Non mi piace che ci sia il parametro $toSmartyAssign. Vedere se è possibile far tornare sempre la raw del recordset
     *  e poi eventualmente fare esternamente l'assegnazione alle variabili di Smart
     */
    private function getImporteroneConnectProduct($id_product = null, $toSmartyAssign = false)
    {
        if ($id_product == null) {
            $id_product = (int)Tools::getValue('id_product');
        }
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'importerone6connect_products WHERE id_product = ' . (int)$id_product; //.' AND id_shop = '.(int)$this->context->shop->id;

        $results = DB::getInstance()->executeS(pSQL($sql));
        if ($results === false) {
            $this->context->controller->errors[] = $this->l('There was an error while loading the data');
        }



        if (count($results) > 0) {
            if ($toSmartyAssign === true) {
                $this->context->smarty->assign(
                    array(
                        'importerone6connect_exclude_sync' => $results[0]['exclude_sync'],
                        'importerone6connect_sync_status' => $results[0]['sync_status'],
                        'importerone6connect_sync_message' => $results[0]['sync_message'],
                        'importerone6connect_manage_title' => $results[0]['manage_title'],
                        'importerone6connect_manage_shortdescription' => $results[0]['manage_shortdescription'],
                        'importerone6connect_manage_description' => $results[0]['manage_description'],
                        'importerone6connect_manage_categories' => $results[0]['manage_categories'],
                        'importerone6connect_manage_prices' => $results[0]['manage_prices'],
                        'importerone6connect_manage_images' => $results[0]['manage_images'],
                        'importerone6connect_manage_features' => $results[0]['manage_features'],
                        'importerone6connect_manage_htmlfeatures' => $results[0]['manage_htmlfeatures'],
                        'importerone6connect_template_htmlfeatures' => $results[0]['template_htmlfeatures'],
                        'importerone6connect_manage_tax_rule' => $results[0]['manage_tax_rule'],
                        'importerone6connect_htmlfeatures' => $results[0]['htmlfeatures'],
                    )
                );
            } else {
                if (isset($results[0])) {
                    return $results[0];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * Renderizza la Scheda HTML del Prodotto
     * {@inheritdoc}
     */
    public function renderWidget($hookName, array $configuration)
    {
        $variables = $this->getWidgetVariables($hookName, $configuration);

        if (empty($variables)) {
            return false;
        }

        $this->smarty->assign($variables);

        return $this->fetch(
            'module:ps_connect_io6/views/templates/front/displayHtmlFeatures.tpl'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetVariables($hookName, array $configuration)
    {
        $id_product = isset($configuration['product']) ? $configuration['product']->id : Tools::getValue('id_product');

        if (!empty($id_product)) {
            $io6ConfigProduct = $this->getImporteroneConnectProduct($id_product);
            if (!empty($io6ConfigProduct['htmlfeatures']))
                return array(
                    'htmlfeatures' => $io6ConfigProduct['htmlfeatures']
                );
        }

        return false;
    }


    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = Tools::getValue('id_product'); //PS 1.6
        if ($id_product == '')
            $id_product = $params['id_product']; //PS 1.7

        if (Validate::isLoadedObject($product = new Product((int)$id_product))) {
            $this->context->smarty->assign(array(
                'id_product' => $id_product,
            ));

            $this->context->smarty->assign(array(
                'default_values' => $this->getConfigFormValuesProductsSettings() //Configurazioni Default
            ));

            $this->getImporteroneConnectProduct($id_product, true); //Configurazione prodotto
            return $this->display(__FILE__, 'views/templates/admin/displayAdminProductsExtra.tpl');
        }
        return "Product NOT Loaded";
    }

    /** Process the product saved **/
    public function hookActionProductUpdate($params)
    {
        $id_product = Tools::getValue('id_product'); //PS 1.6
        if ($id_product == '')
            $id_product = $params['id_product']; //PS 1.7

        $form_data = Tools::getValue('importerone6connect');

        if ($form_data === false)
            return; //Continuo solo se salvo da pagina prodotto

        $sync_status = '';
        $sync_message  = '';
        $exclude_sync  = (isset($form_data['importerone6connect_exclude_sync']) ? (int)$form_data['importerone6connect_exclude_sync'] : 0);
        $manage_title  = ($form_data['importerone6connect_manage_title'] != '' ? (int)$form_data['importerone6connect_manage_title'] : 2);
        $manage_shortdescription  = ($form_data['importerone6connect_manage_shortdescription'] != '' ? (int)$form_data['importerone6connect_manage_shortdescription'] : 2);
        $manage_description  = ($form_data['importerone6connect_manage_description'] != '' ? (int)$form_data['importerone6connect_manage_description'] : 2);
        $manage_categories  = ($form_data['importerone6connect_manage_categories'] != '' ? (int)$form_data['importerone6connect_manage_categories'] : 2);
        $manage_prices  = ($form_data['importerone6connect_manage_prices'] != '' ? (int)$form_data['importerone6connect_manage_prices'] : 2);
        $manage_images  = ($form_data['importerone6connect_manage_images'] != '' ? (int)$form_data['importerone6connect_manage_images'] : 2);
        $manage_features  = ($form_data['importerone6connect_manage_features'] != '' ? (int)$form_data['importerone6connect_manage_features'] : 2);
        $manage_htmlfeatures  = ($form_data['importerone6connect_manage_htmlfeatures'] != '' ? (int)$form_data['importerone6connect_manage_htmlfeatures'] : 2);
        $template_htmlfeatures  = ($form_data['importerone6connect_template_htmlfeatures'] != '' ? (int)$form_data['importerone6connect_template_htmlfeatures'] : 2);
        $manage_tax_rule = ($form_data['importerone6connect_manage_tax_rule'] != '' ? (int)$form_data['importerone6connect_manage_tax_rule'] : 2);
        $htmlfeatures  = ''; //$form_data['importerone6connect_htmlfeatures'];

        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'importerone6connect_products` 
                (`id_product`, `sync_status`, `sync_message`, `exclude_sync`, `manage_title`, `manage_shortdescription`, `manage_description`,
                `manage_categories`, `manage_prices`, `manage_images`, `manage_features`, `manage_htmlfeatures`, `manage_tax_rule`, `htmlfeatures`)
                VALUES (
                    ' . (int)$id_product . ',
                    ' . (int)$sync_status . ',
                    \'' . pSQL($sync_message) . '\',
                    ' . (int)$exclude_sync . ',
                    ' . (int)$manage_title . ',
                    ' . (int)$manage_shortdescription . ',
                    ' . (int)$manage_description . ',
                    ' . (int)$manage_categories . ',
                    ' . (int)$manage_prices . ',
                    ' . (int)$manage_images . ',
                    ' . (int)$manage_features . ',
                    ' . (int)$manage_htmlfeatures . ',
                    ' . (int)$manage_tax_rule . ',
                    \'' . pSQL($htmlfeatures) . '\'
                ) ON DUPLICATE KEY UPDATE 
                    `exclude_sync` = ' . (int)$exclude_sync . ',
                    `manage_title` = ' . (int)$manage_title . ',
                    `manage_shortdescription` = ' . (int)$manage_shortdescription . ',
                    `manage_description` = ' . (int)$manage_description . ',
                    `manage_categories` = ' . (int)$manage_categories . ',
                    `manage_prices` = ' . (int)$manage_prices . ',
                    `manage_images` = ' . (int)$manage_images . ',
                    `manage_features` = ' . (int)$manage_features . ',
                    `manage_htmlfeatures` = ' . (int)$manage_htmlfeatures . ',
                    `manage_tax_rule` = ' . (int)$manage_tax_rule;

        //die($sql);
        if (!Db::getInstance()->execute($sql)) {
            $this->context->controller->errors[] = Tools::displayError('Error: ') . Db::getInstance()->getNumberError() . Db::getInstance()->getMsgError();
        }
    }

    public function generateImgIntoCms($id_entity, $cms_image = null, $sourcePath, $entity = 'products')
    {
        $tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');

        try {
            //$sourcePath = str_replace(' ', '%20', trim($sourcePath));

            switch ($entity) {
                default:
                case 'products':
                    //$image_obj = new Image($cms_image);
                    $path = $cms_image->getPathForCreation();
                    break;
                case 'categories':
                    $path = _PS_CAT_IMG_DIR_ . (int)$id_entity;
                    break;
                case 'manufacturers':
                    $path = _PS_MANU_IMG_DIR_ . (int)$id_entity;
                    break;
                case 'suppliers':
                    $path = _PS_SUPP_IMG_DIR_ . (int)$id_entity;
                    break;
                case 'stores':
                    $path = _PS_STORE_IMG_DIR_ . (int)$id_entity;
                    break;
            }

            if (@copy($sourcePath, $tmpfile)) { //TODO CT 20210517 Potrebbe non servire questa copia di file, già si sta lavorando su un file scaricato salvato nella cartella di appoggio in 'io6-images'
                // $this->echoDebug("estensione immagine:" . pathinfo($sourcePath, PATHINFO_EXTENSION));
                if (strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION)) == "png") { //converto PNG in JPG
                    // $this->echoDebug("conversione PNG in JPG...");
                    $input = imagecreatefrompng($tmpfile);
                    list($width, $height) = getimagesize($tmpfile);
                    $output = imagecreatetruecolor($width, $height);
                    $white = imagecolorallocate($output,  255, 255, 255);
                    imagefilledrectangle($output, 0, 0, $width, $height, $white);
                    imagecopy($output, $input, 0, 0, 0, 0, $width, $height);
                    imagejpeg($output, $tmpfile);
                    // $this->echoDebug("conversione PNG in JPG terminata");
                }

                // $this->echoDebug("Verifica stima memoria per gestione immagine ...");
                $infos = @getimagesize($tmpfile);
                if (!is_array($infos) || !isset($infos['bits'])) {
                    // $this->echoDebug("info non recuperate su immagine", IO_LEVEL_WARNING);
                } else {
                    $memory_limit = Tools::getMemoryLimit();
                    if (function_exists('memory_get_usage') && (int)$memory_limit > 0) {
                        $current_memory = memory_get_usage();
                        $channel = isset($infos['channels']) ? ($infos['channels'] / 8) : 1;

                        // Evaluate the memory required to resize the image: if it's too much, you can't resize it.
                        // For perfs, avoid computing static maths formulas in the code. pow(2, 16) = 65536 ; 1024 * 1024 = 1048576
                        $memory_evaluated = (($infos[0] * $infos[1] * $infos['bits'] * $channel + 65536) * 1.8 + $current_memory);
                        //if (($infos[0] * $infos[1] * $infos['bits'] * $channel + 65536) * 1.8 + $current_memory > $memory_limit - 1048576) {
                        if ($memory_evaluated > ($memory_limit / 3)) { //La previsione di utilizzo ram non deve superare la metà del memory_limit
                            // $this->echoDebug("memoria stimata per gestione immagine: " . $this->formatByte($memory_evaluated), IO_LEVEL_WARNING);
                            // $this->echoDebug("memory_limit:" . $this->formatByte($memory_limit), IO_LEVEL_WARNING);
                            // $this->echoDebug("memoria non sufficiente per gestione dell'immagine", IO_LEVEL_WARNING);
                            if (file_exists($tmpfile))
                                unlink($tmpfile);
                            return false;
                        }
                    }
                }

                // $this->echoDebug("ridimensiona immagini ...");
                // $this->echoDebug("path:" . $path . '.jpg' . "...");
                ImageManager::resize($tmpfile, $path . '.jpg');
                $images_types = ImageType::getImagesTypes($entity);
                foreach ($images_types as $image_type) {
                    // $this->echoDebug("ridimensiona immagini image_type:" . $image_type['name'] . "...");
                    ImageManager::resize($tmpfile, $path . '-' . stripslashes($image_type['name']) . '.jpg', $image_type['width'], $image_type['height']);
                }
            } else {
                unlink($tmpfile);
                return false;
            }
            unlink($tmpfile);
            return true;
        } catch (Exception $ex) {
            if (file_exists($tmpfile))
                unlink($tmpfile);
            // $this->echoDebug("copyImg non riuscita:" . $ex->getMessage(), IO_LEVEL_WARNING);
            return false;
        }
    }

    function getSpecificPricesByImporterONE($id_product)
    {
        $specifiPrices = array();
        if (empty($id_product))
            return $specifiPrices;
        $sql = "SELECT * FROM " . _DB_PREFIX_ . "importerone6connect_specific_price iosp "
            . " WHERE iosp.id_product = " . pSQL((int)$id_product);
        if ($results = DB::getInstance()->executeS($sql)) {
            foreach ($results as $row)
                $specifiPrices[] = $row['id_specific_price'];
        }
        return $specifiPrices;
    }
    function getSpecificPricesCmsAll($id_product)
    {
        $specifiPrices = array();
        if (empty($id_product))
            return $specifiPrices;
        $sql = "SELECT * FROM " . _DB_PREFIX_ . "specific_price sp "
            . " WHERE sp.id_product = " . pSQL((int)$id_product) . " AND sp.id_specific_price_rule = 0 AND sp.id_cart = 0";
        if ($results = DB::getInstance()->executeS($sql)) {
            foreach ($results as $row)
                $specifiPrices[] = $row['id_specific_price'];
        }
        return $specifiPrices;
    }
    function getSpecificPricesNotImporterONE($id_product)
    {
        $specifiPrices = array();
        if (empty($id_product))
            return $specifiPrices;
        $sql = "SELECT sp.* FROM " . _DB_PREFIX_ . "specific_price sp LEFT JOIN " . _DB_PREFIX_ . "importerone6connect_specific_price iosp ON sp.id_specific_price = iosp.id_specific_price "
            . " WHERE sp.id_product = " . pSQL((int)$id_product) . " AND sp.id_specific_price_rule = 0 AND sp.id_cart = 0 AND iosp.id_product IS NULL ";
        if ($results = DB::getInstance()->executeS($sql)) {
            foreach ($results as $row)
                $specifiPrices[] = $row['id_specific_price'];
        }
        return $specifiPrices;
    }
    function saveSpecificPricecsByImporterONE($id_product, $ids_specific_price)
    {
        if (empty($id_product)) {
            return false;
        }
        Db::getInstance()->delete('importerone6connect_specific_price', 'id_product = ' . (int)$id_product);
        if (is_array($ids_specific_price) and sizeof($ids_specific_price)) {
            $data = array();
            foreach ($ids_specific_price as $id_specific_price) {
                $data[] = array(
                    'id_product' => (int)$id_product,
                    'id_specific_price' => (int)$id_specific_price
                );
            }
            return Db::getInstance()->insert('importerone6connect_specific_price', $data);
        }
    }

    public function io6_write_log($log, $level)
    {
        $logFile = IO6_LOG_DIRPATH . date('Ymd') . ".txt";
        $time = date('Y-m-d H:i:s');
        $logMessage = sprintf("%s - %s: %s\r\n", $time, $level, (is_array($log) || is_object($log) ? print_r($log, true) : $log));

        error_log($logMessage, 3, $logFile);
    }
}

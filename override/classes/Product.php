<?php
class Product extends ProductCore
{

	public function getImages($id_lang, Context $context = null)
	{
		$delayedDownloadsIimages = Configuration::get('IMPORTERONE6CONNECT_DELAYED_DOWNLOADS_IMAGES');
		if ((int)$delayedDownloadsIimages == 1) 
			Product::getIO6DelayedImages((int)$this->id, 0); //Limit 0 = Tutte le immagini
		
		return parent::getImages($id_lang, $context);
	}

	/**
	 * Get product cover image
	 *
	 * @return array Product cover image
	 */
	public static function getCover($id_product, Context $context = null)
	{
		if (!$context) {
			$context = Context::getContext();
		}

		$result = parent::getCover($id_product, $context);
		if ($result === false) {
			$delayedDownloadsImages = Configuration::get('IMPORTERONE6CONNECT_DELAYED_DOWNLOADS_IMAGES');
			if ((int)$delayedDownloadsImages == 1) {
				if($context->controller->controller_type == 'admin'){
					Product::getIO6DelayedImages($id_product, 0); //Limit 1 = solo una immagine
				} else {
					Product::getIO6DelayedImages($id_product, 1); //Limit 1 = solo una immagine
				}
				$cache_id = 'Product::getCover_' . (int)$id_product . '-' . (int)$context->shop->id;
				Cache::clean($cache_id);
				$result = parent::getCover($id_product, $context);
			}
		}
		return $result;
	}

	public static function getIO6DelayedImages($id_product, $limit)
	{

		try {
			if (!Module::isEnabled("ps_connect_io6")) {
				//die("Module ps_connect_io6 not enabled");
				return;
			}
			

			// $sql = 'SELECT ioI.`id_product`, ioI.`image_path`,ioI.`id_image`, ioI.`orderindex`, ioI.`timestamp` FROM `' . _DB_PREFIX_ . 'io_images` ioI
			// 			WHERE  ioI.id_product =' . $id_product . ' AND ioI.id_image = 0 ORDER BY ioI.`orderindex`
			// 			' . ($limit > 0 ? ' LIMIT ' . $limit : '') . '; ';
			$sql = 'SELECT id_product, id_image, image_uri, orderindex, lastupdate FROM '._DB_PREFIX_.'importerone6connect_images 
						WHERE id_product = ' . (int)$id_product . ' AND id_image = 0 ORDER BY orderindex ASC
						' . ($limit > 0 ? ' LIMIT ' . $limit : '') . '; ';
	

			$io6ConnectGallery = Db::getInstance()->ExecuteS($sql);
			if (is_array($io6ConnectGallery) && count($io6ConnectGallery)) {
				$importerone6connectModule = Module::getInstanceByName("ps_connect_io6");

				foreach ($io6ConnectGallery as $k => $io6image) {
					try {
						$image_data     = file_get_contents($io6image['image_uri']); // Get image data								
						if (!isset($image_data)) continue;

						$image_filename = date('YmdHis') . '_' . basename($io6image['image_uri']);
						$image_filepath = IO6_IMAGES_DIRPATH . $image_filename;

						file_put_contents($image_filepath, $image_data);

						$cms_image = new Image();
						$cms_image->id_product = $id_product;
						$cms_image->position = Image::getHighestPosition($id_product) + 1;
						$cms_image->cover = ((bool)Image::getCover($id_product)) ? 0 : 1;
						if ($cms_image->add()) {
							//$cms_image->associateTo((int)(Context::getContext()->shop->id)); //Non serve perchè il metodo ->add già associa in automatico a tutti gli shop

							if (@$importerone6connectModule->generateImgIntoCms((int)$id_product, $cms_image, $image_filepath, 'products')) {
								// $this->echoDebug("added image=" . $image_filepath . " id product=" . $id_product, IO_LEVEL_DEBUG);
								Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'importerone6connect_images` SET id_image = ' . (int)$cms_image->id 
									. ' WHERE id_product = ' .(int)$id_product . ' AND image_uri = \'' . pSQL($io6image['image_uri']) . '\'');
							} else {
								$cms_image->delete(); //Rimuovo anche anagrafica immagine appena inserita
								// $this->echoDebug("generateImgIntoCms non riuscita. product_image_url=" . $image_filepath . " id product=" . $id_product, IO_LEVEL_WARNING);
							}
						} 
						else {								
						}

						@unlink($image_filepath);
					}
					catch (Exception $e) { 
						if(isset($cms_image))
							$cms_image->delete();
						if (file_exists($image_filepath))
							unlink($image_filepath);
					}

				}
			}
		} catch (Exception $e) { }
	}

}

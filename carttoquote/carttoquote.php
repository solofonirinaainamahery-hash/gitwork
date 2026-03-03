<?php
/**
 * Cart to Quote (Version 1.0.0)
 * Transforme le panier en devis via le module pricequote (Lineven)
 * Inverse exact de pqtocart
 *
 * @author    Custom
 * @copyright 2024
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartToQuote extends Module
{
    public function __construct()
    {
        $this->name          = 'carttoquote';
        $this->tab           = 'front_office_features';
        $this->version       = '1.0.0';
        $this->author        = 'Custom';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Cart to Quote');
        $this->description = $this->l('Transforme le panier en devis pricequote et vide le panier.');
    }

    public function install()
    {
        // Vérifier que pricequote est installé
        if (!Module::isInstalled('pricequote')) {
            $this->_errors[] = $this->l('Le module pricequote doit être installé pour utiliser ce module.');
            return false;
        }

        return parent::install()
            && $this->registerHook('displayShoppingCartFooter')
            && $this->registerHook('displayHeader');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/carttoquote.css');
        $this->context->controller->addJS($this->_path . 'views/js/carttoquote.js');
    }

    public function hookDisplayShoppingCartFooter($params)
    {
        $cart = $this->context->cart;

        if (!$cart || !count($cart->getProducts())) {
            return '';
        }

        $transformUrl = $this->context->link->getModuleLink(
            'carttoquote',
            'transform',
            [],
            true
        );

        $this->context->smarty->assign([
            'ctq_transform_url' => $transformUrl,
            'ctq_nb_products'   => count($cart->getProducts()),
        ]);

        return $this->display(__FILE__, 'views/templates/front/button.tpl');
    }
}

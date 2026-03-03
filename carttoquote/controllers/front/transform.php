<?php
/**
 * Contrôleur transform — Cart → Quote (pricequote Lineven)
 * Inverse exact de pqtocart/controllers/front/transform.php
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartToQuoteTransformModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;

        // Panier vide ou inexistant → retour panier
        if (!$cart || !$cart->id) {
            Tools::redirect($this->context->link->getPageLink('cart'));
        }

        $products = $cart->getProducts();

        if (empty($products)) {
            Tools::redirect($this->context->link->getPageLink('cart'));
        }

        // Chargement des classes du module pricequote (Lineven)
        $pricequotePath = _PS_MODULE_DIR_ . 'pricequote/pricequote.php';
        $quotationPath  = _PS_MODULE_DIR_ . 'pricequote/classes/quotation/Quotation.php';

        if (!file_exists($pricequotePath) || !file_exists($quotationPath)) {
            Tools::redirect($this->context->link->getPageLink('cart'));
        }

        require_once($pricequotePath);
        require_once($quotationPath);

        // -------------------------------------------------------
        // Récupération ou création du devis via getQuotation()
        // C'est la méthode officielle de pricequote pour obtenir
        // le devis en cours de session (lit les cookies)
        // On passe save_quotation=true pour qu'il crée + sauvegarde
        // automatiquement le devis et écrive les cookies
        // -------------------------------------------------------
        $quotation = LinevenPqtQuotation::getQuotation(null, null, true);

        if (!$quotation || !$quotation->id) {
            Tools::redirect($this->context->link->getPageLink('cart'));
        }

        // -------------------------------------------------------
        // Ajout des produits du panier dans le devis
        // La vraie méthode est updateProduct() — vue dans Quotation.php
        // Elle fait INSERT si le produit n'existe pas, UPDATE sinon
        // -------------------------------------------------------
        foreach ($products as $product) {
            $quotation->updateProduct(
                (int) $product['id_product'],
                (int) ($product['id_product_attribute'] ?? 0),
                0,                              // id_customization
                (int) $product['cart_quantity'],
                'up'                            // opérateur : ajoute la quantité
            );
        }

        // -------------------------------------------------------
        // Vidage du panier
        // (inverse de dispose() dans pqtocart qui efface le devis)
        // -------------------------------------------------------
        foreach ($products as $product) {
            $cart->deleteProduct(
                (int) $product['id_product'],
                (int) ($product['id_product_attribute'] ?? 0)
            );
        }

        // -------------------------------------------------------
        // Redirection vers la page devis pricequote
        // Même URL que le point de départ dans pqtocart
        // -------------------------------------------------------
        Tools::redirect('index.php?fc=module&module=pricequote&controller=pricequote');
    }
}

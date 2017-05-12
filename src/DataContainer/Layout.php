<?php

/**
 * @package   contao-bootstrap
 * @author    David Molineus <david.molineus@netzmacht.de>
 * @license   LGPL 3+
 * @copyright 2013-2017 netzmacht creative David Molineus
 */

namespace ContaoBootstrap\Core\DataContainer;

use Bit3\Contao\MetaPalettes\MetaPalettes;
use ContaoBootstrap\Core\Config;
use Input;
use LayoutModel;

/**
 * Class Layout is used in tl_layout.
 *
 * @package ContaoBootstrap\Core\DataContainer
 */
class Layout
{
    /**
     * Bootstrap config.
     *
     * @var Config
     */
    private $config;

    /**
     * Settings constructor.
     */
    public function __construct()
    {
        // TODO: Use Dependency injection
        $this->config = \Controller::getContainer()->get('contao_bootstrap.config');
    }

    /**
     * Modify palette if bootstrap is used.
     *
     * Hook palettes_hook (MetaPalettes) is called.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function generatePalette()
    {
        // @codingStandardsIgnoreStart
        // TODO: How to handle editAll actions?
        // @codingStandardsIgnoreEnd

        if (Input::get('table') != 'tl_layout' || Input::get('act') != 'edit') {
            return;
        }

        $layout = LayoutModel::findByPk(Input::get('id'));

        // dynamically render palette so that extensions can plug into default palette
        if ($layout->layoutType == 'bootstrap') {
            $metaPalettes                             = & $GLOBALS['TL_DCA']['tl_layout']['metapalettes'];
            $metaPalettes['__base__']                 = $this->getMetaPaletteOfPalette('tl_layout');
            $metaPalettes['default extends __base__'] = $this->config->get('layout.metapalette', array());

            // unset default palette. otherwise metapalettes will not render this palette
            unset($GLOBALS['TL_DCA']['tl_layout']['palettes']['default']);

            $subSelectPalettes = $this->config->get('layout.metasubselectpalettes', array());

            foreach ($subSelectPalettes as $field => $meta) {
                foreach ($meta as $value => $definition) {
                    unset($GLOBALS['TL_DCA']['tl_layout']['subpalettes'][$field . '_' . $value]);
                    $GLOBALS['TL_DCA']['tl_layout']['metasubselectpalettes'][$field][$value] = $definition;
                }
            }
        } else {
            MetaPalettes::appendFields('tl_layout', 'title', array('layoutType'));
        }
    }

    /**
     * Creates an meta palette of a palettes.
     *
     * @param string $table Database table name.
     * @param string $name  Palette name.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getMetaPaletteOfPalette($table, $name = 'default')
    {
        $palette     = $GLOBALS['TL_DCA'][$table]['palettes'][$name];
        $metaPalette = array();
        $legends     = explode(';', $palette);

        foreach ($legends as $legend) {
            $fields = explode(',', $legend);

            preg_match('/\{(.*)_legend(:hide)?\}/', $fields[0], $matches);

            if (isset($matches[2])) {
                $fields[0] = $matches[2];
            } else {
                array_shift($fields);
            }

            $metaPalette[$matches[1]] = $fields;
        }

        return $metaPalette;
    }
}
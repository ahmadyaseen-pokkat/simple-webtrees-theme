<?php

declare(strict_types=1);

namespace BeginnerFriendly\Webtrees\Module\BeginnerFriendlyTheme;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\View;
use Fisharebest\Webtrees\Session;
use Fisharebest\Webtrees\FlashMessages;
use Psr\Http\Message\ResponseInterface;
use Fisharebest\Localization\Translation;
use Psr\Http\Message\ServerRequestInterface;
use Fisharebest\Webtrees\Module\MinimalTheme;
use Illuminate\Database\Capsule\Manager as DB;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Module\ModuleThemeTrait;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleFooterTrait;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Module\ModuleThemeInterface;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleFooterInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;

class BeginnerFriendlyTheme extends MinimalTheme implements ModuleThemeInterface, ModuleCustomInterface, ModuleFooterInterface, ModuleGlobalInterface, ModuleConfigInterface
{
    use ModuleThemeTrait;
    use ModuleCustomTrait;
    use ModuleFooterTrait;
    use ModuleGlobalTrait;
    use ModuleConfigTrait;

    // Module constants
    public const CUSTOM_AUTHOR = 'Beginner Friendly';
    public const CUSTOM_VERSION = '2.4.0';
    public const GITHUB_REPO = 'webtrees-theme-justlight';
    public const AUTHOR_WEBSITE = 'justcarmen.nl';
    public const CUSTOM_SUPPORT_URL = 'https://' . self::AUTHOR_WEBSITE . '/modules-webtrees-2/justlight-theme/';

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     */
    public function title(): string
    {
        return I18N::translate('Beginner Friendly Theme');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleAuthorName()
     */
    public function customModuleAuthorName(): string
    {
        return self::CUSTOM_AUTHOR;
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleVersion()
     */
    public function customModuleVersion(): string
    {
        return self::CUSTOM_VERSION;
    }

    /**
     * A URL that will provide the latest stable version of this module.
     *
     * @return string
     */
    public function customModuleLatestVersionUrl(): string
    {
        return 'https://raw.githubusercontent.com/' . self::CUSTOM_AUTHOR . '/' . self::GITHUB_REPO . '/main/latest-version.txt';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleSupportUrl()
     */
    public function customModuleSupportUrl(): string
    {
        return self::CUSTOM_SUPPORT_URL;
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::boot()
     */
    public function boot(): void
    {
        // Register a namespace for our views.
        View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');

        // Add all javascript used by this module in a view
        View($this->name() . '::theme/script.js');

        // Replace an existing view with our own version.
        View::registerCustomView('::individual-page-images', $this->name() . '::individual-page-images');
        View::registerCustomView('::report-setup-page', $this->name() . '::report-setup-page');
        View::registerCustomView('::icons/add', $this->name() . '::icons/add');
        View::registerCustomView('::icons/anniversary', $this->name() . '::icons/anniversary');
        View::registerCustomView('::icons/calendar', $this->name() . '::icons/calendar');
        View::registerCustomView('::icons/delete', $this->name() . '::icons/delete');
        View::registerCustomView('::icons/individual', $this->name() . '::icons/individual');
        View::registerCustomView('::layouts/default', $this->name() . '::layouts/default');
        View::registerCustomView('::modules/clippings/show', $this->name() . '::modules/clippings/show');
        View::registerCustomView('::modules/faq/show', $this->name() . '::modules/faq/show');
        View::registerCustomView('::modules/favorites/favorites', $this->name() . '::modules/favorites/favorites');
        View::registerCustomView('::modules/gedcom_news/list', $this->name() . '::modules/gedcom_news/list');
        View::registerCustomView('::modules/gedcom_stats/statistics', $this->name() . '::modules/gedcom_stats/statistics');
        View::registerCustomView('::modules/lightbox/tab', $this->name() . '::modules/lightbox/tab');
        View::registerCustomView('::modules/stories/list', $this->name() . '::modules/stories/list');
        View::registerCustomView('::modules/user_blog/list', $this->name() . '::modules/user_blog/list');
        View::registerCustomView('::modules/user-messages/user-messages', $this->name() . '::modules/user-messages/user-messages');

        // specific views for the JustBlack palette
        if ($this->palette() === 'justblack') {
            View::registerCustomView('::statistics/other/charts/column', $this->name() . '::statistics/other/charts/column');
            View::registerCustomView('::statistics/other/charts/combo', $this->name() . '::statistics/other/charts/combo');
            View::registerCustomView('::statistics/other/charts/geo', $this->name() . '::statistics/other/charts/geo');
            View::registerCustomView('::statistics/other/charts/pie', $this->name() . '::statistics/other/charts/pie');
        }
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::resourcesFolder()
     */
    public function resourcesFolder(): string
    {
        return __DIR__ . '/resources/';
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getAdminAction(): ResponseInterface
    {
        if (Session::get('theme') !== $this->name()) {
            // We need to register the namespace for this view because the boot didn't run
            View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');
        }

        $this->layout = 'layouts/administration';

        return $this->viewResponse($this->name() . '::settings', [
            'allow_switch' => $this->getPreference('allow-switch', '0'),
            'palette'      => $this->getPreference('palette', 'justlight'),
            'palettes'     => $this->palettes(),
            'title'        => $this->title()
        ]);
    }

    /**
     * Save the user preference.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function postAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = (array) $request->getParsedBody();

        if ($params['save'] === '1') {

            if ($params['allow-switch'] === '0') {
                // remove any previous set users' palette choice.
                DB::table('user_setting')->where('setting_name', '=', 'justlight-palette')->delete();
            }

            $this->setPreference('palette', $params['palette']);
            $this->setPreference('allow-switch', $params['allow-switch']);

            $message = I18N::translate('The preferences for the module “%s” have been updated.', $this->title());
            FlashMessages::addMessage($message, 'success');

        }

        return redirect($this->getConfigLink());
    }

    /**
     * Raw content, to be added at the end of the <head> element.
     * Typically, this will be <link> and <meta> elements.
     * we use it to load the special font files
     *
     * @return string
     */
    public function headContent(): string {
        // Only load custom JavaScript when this theme is active
        if (Session::get('theme') !== $this->name()) {
            return '';
        }



        return
            '<style>
            @font-face {
                font-family: \'icomoon\';
                src: url("' . $this->assetUrl('fonts/icomoon.eot') . '");
                src: url("' . $this->assetUrl('fonts/icomoon.eot') . ' #iefix") format("embedded-opentype"),
                    url("' . $this->assetUrl('fonts/icomoon.ttf') . '") format("truetype"),
                    url("' . $this->assetUrl('fonts/icomoon.woff') . '") format("woff"),
                    url("' . $this->assetUrl('fonts/icomoon.svg') . ' #icomoon") format("svg");
                font-weight: normal;
                font-style: normal;
                font-display: block;
            }
            </style>
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Hide specific descendants chart
                const menuItems = document.querySelectorAll(".dropdown-menu a.menu-chart-descendants");
                menuItems.forEach(function(item) {
                    const text = item.textContent.trim();
                    if (text === "Descendants") {
                        item.style.display = "none";
                    }
                });
                
                // Hide Lists, Calendar, and Reports dropdown menus
                const navItems = document.querySelectorAll(".nav-item.dropdown");
                navItems.forEach(function(item) {
                    const navLink = item.querySelector("a.nav-link");
                    if (navLink) {
                        const text = navLink.textContent.trim();
                        if (text === "Calendar" || text === "Reports" || text === "Clippings cart") {
                            item.style.display = "none";
                        }
                    }
                });
                
                // Hide Research Links from individual page sidebars
                const todoHeader = document.getElementById("sidebar-header-todo");
                if (todoHeader) {
                    const todoItem = todoHeader.closest(".accordion-item");
                    if (todoItem) {
                        todoItem.style.display = "none";
                    }
                }
                
                const fancyResearchHeader = document.getElementById("sidebar-header-_jc-fancy-research-links_");
                if (fancyResearchHeader) {
                    const fancyItem = fancyResearchHeader.closest(".accordion-item");
                    if (fancyItem) {
                        fancyItem.style.display = "none";
                    }
                }
                
                // Hide "Unique identifier" and "Record ID number" fields from individual pages
                const labels = document.querySelectorAll(".wt-fact-other-attributes span.label");
                labels.forEach(function(label) {
                    const labelText = label.textContent.trim();
                    if (labelText === "Unique identifier" || labelText === "Record ID number") {
                        // Hide the parent div containing both label and value
                        const parentDiv = label.closest("div");
                        if (parentDiv) {
                            parentDiv.style.display = "none";
                        }
                    }
                });
                
                // Hide ALL MyHeritage FTB _TYPE junk from individual facts
                // This targets _TYPE fields that appear in residence and other facts
                const allLabels = document.querySelectorAll("span.label");
                allLabels.forEach(function(label) {
                    const labelText = label.textContent.trim();
                    // Check if label contains _TYPE or is exactly "_TYPE"
                    if (labelText === "_TYPE" || labelText.includes("_TYPE")) {
                        const parentDiv = label.closest("div");
                        if (parentDiv) {
                            parentDiv.style.display = "none";
                        }
                    }
                    
                    // Hide "Record ID" labels that contain "MH:" pattern
                    if (labelText === "Record ID" || labelText === "Record ID number") {
                        const parentDiv = label.closest("div");
                        const valueSpan = parentDiv ? parentDiv.querySelector("span.value") : null;
                        if (valueSpan && valueSpan.textContent.includes("MH:")) {
                            parentDiv.style.display = "none";
                        }
                    }
                });
                
                // Also hide any values that contain "_TYPE:" pattern (like "_TYPE: Home")
                const allValues = document.querySelectorAll("span.value");
                allValues.forEach(function(valueSpan) {
                    const valueText = valueSpan.textContent.trim();
                    if (valueText.includes("_TYPE:") || valueText.startsWith("_TYPE")) {
                        const parentDiv = valueSpan.closest("div");
                        if (parentDiv) {
                            parentDiv.style.display = "none";
                        }
                    }
                });
                
                // Clean up MyHeritage physical descriptions (DESC fields)
                // These often contain codes like "DESC-HEIGHT-cm" that are confusing for beginners
                allLabels.forEach(function(label) {
                    const labelText = label.textContent.trim();
                    if (labelText === "Description" || labelText.includes("Physical description")) {
                        const parentDiv = label.closest("div");
                        if (parentDiv) {
                            const valueSpan = parentDiv.querySelector("span.value");
                            if (valueSpan) {
                                const valueText = valueSpan.textContent;
                                // Check if it contains MyHeritage DESC codes
                                if (valueText.includes("DESC-") || valueText.includes("-HEIGHT-") || 
                                    valueText.includes("-HAIR-") || valueText.includes("-EYES-")) {
                                    // Replace the technical codes with a simple message
                                    valueSpan.innerHTML = "<em style=\"color: #666; font-size: 0.9em;\">Physical description from source record</em>";
                                    valueSpan.style.fontFamily = "Segoe UI, sans-serif";
                                    valueSpan.style.lineHeight = "1.4";
                                }
                            }
                        }
                    }
                });
            });
            </script>';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::stylesheets()
     */
    public function stylesheets(): array
    {
        // Only load beginner.css when this theme is active
        if (Session::get('theme') !== $this->name()) {
            return parent::stylesheets();
        }


        $color_scheme = isset($_COOKIE["JL_COLOR_SCHEME"]) ? $_COOKIE["JL_COLOR_SCHEME"] : false;
        if ($color_scheme === false) $color_scheme = 'light';  // fallback

        // Load the CSS for the correct color-scheme
        if ($this->palette() === 'justauto') {
            if ($color_scheme === 'dark') {
                $stylesheet = $this->assetUrl('css/justblack.min.css');
            } else {
                $stylesheet = $this->assetUrl('css/justlight.min.css');
            }
        } else {
            $stylesheet = $this->assetUrl('css/' . $this->palette() . '.min.css');
        }

        return [$stylesheet, $this->assetUrl('css/beginner.css')];
    }

    /**
     * A footer, to be added at the bottom of every page.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function getFooter(ServerRequestInterface $request): string
    {
        if (Session::get('theme') === $this->name()) {
            return view($this->name() . '::theme/footer-credits', [
                'url' => 'https://' . self::AUTHOR_WEBSITE,
                'text' => 'Design: ' . self::AUTHOR_WEBSITE,
            ]);
        } else {
            return "";
        }
    }


    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleThemeInterface::stylesheets()
     * Usage: FanChartModule and Statistics charts
     */
    public function parameter($parameter_name)
    {
        $parameters1 = [
            'chart-background-f'             => 'fff0f5', // FanChart
            'chart-background-m'             => 'd7eaf9', // Fanchart
            'chart-background-u'             => 'f9f9f9', // Fanchart
            'chart-box-x'                    => 260, // unused
            'chart-box-y'                    => 85, // unused
            'chart-font-color'               => '212529', // FanChart
            'chart-spacing-x'                => 5, // unused
            'chart-spacing-y'                => 10, // unused
            'compact-chart-box-x'            => 240, // unused
            'compact-chart-box-y'            => 50, // unused
        ];

        if ($this->palette() === 'justblack') {
            $parameters2 = [
                'distribution-chart-high-values' => 'd86400', // Statistics charts (palette primary color)
                'distribution-chart-low-values'  => 'ffca66', // Statistics charts (geo chart only, lighten 40%)
                'distribution-chart-no-values'   => 'dee2e6', // Statistics charts (sass jc-gray-300)
            ];
        } else {
            $parameters2 = [
                'distribution-chart-high-values' => '337ab7', // Statistics charts (palette primary color)
                'distribution-chart-low-values'  => '99e0ff', // Statistics charts (geo chart only, lighten 40%)
                'distribution-chart-no-values'   => 'dee2e6', // Statistics charts ((sass jc-gray-300)
            ];
        }

        $parameters = array_merge($parameters1, $parameters2);

        return $parameters[$parameter_name];
    }

    /**
     * Generate a list of items for the user menu.
     *
     * @param Tree|null $tree
     *
     * @return Menu[]
     */
    public function userMenu(?Tree $tree): array
    {
        if ($this->getPreference('allow-switch')) {
            return array_filter([
                $this->menuPendingChanges($tree),
                $this->menuMyPages($tree),
                $this->menuThemes(),
                $this->menuPalette(),
                $this->menuLanguages(),
                $this->menuLogin(),
                $this->menuLogout(),
            ]);
        } else {
            return parent::userMenu($tree);
        }
    }

    /**
     * Create a menu of palette options
     *
     * @return Menu
     */
    protected function menuPalette(): Menu
    {
        /* I18N: A colour scheme */
        $menu = new Menu(I18N::translate('Palette'), '#', 'menu-justlight');

        $palette = $this->palette();

        foreach ($this->palettes() as $palette_id => $palette_name) {
            $url = route('module', ['module' => $this->name(), 'action' => 'Palette', 'palette' => $palette_id]);

            if ($palette_id === 'justauto') {
                $title = I18N::translate('Automatically chooses a palette based on browser color mode');
            } else {
                $title = "";
            }

            $submenu = new Menu(
                $palette_name,
                '#',
                'menu-justlight-' . $palette_id . ($palette === $palette_id ? ' active' : ''),
                [
                    'data-wt-post-url' => $url,
                    'title' => $title
                ]
            );

            $menu->addSubmenu($submenu);
        }

        return $menu;
    }

     /**
     * Switch to a new palette
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function postPaletteAction(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        assert($user instanceof UserInterface);

        $palette = $request->getQueryParams()['palette'];
        assert(array_key_exists($palette, $this->palettes()));

        $user->setPreference('justlight-palette', $palette);

        Session::put('justlight-palette', $palette);

        return response();
    }

    /**
     * @return array<string>
     */
    private function palettes(): array
    {
        $palettes = [
            /* I18N: The name of a colour-scheme */
            'justlight'       => I18N::translate('JustLight'),
            /* I18N: The name of a colour-scheme */
            'justblack'       => I18N::translate('JustBlack'),
             /* I18N: The name of a colour-scheme */
             'justauto'         => I18N::translate('JustAuto')
        ];

        uasort($palettes, I18N::comparator());

        return $palettes;
    }

    /**
     * @return string
     */
    public function palette(): string
    {
        // If we are logged in, use our preference
        $palette = Auth::user()->getPreference('justlight-palette', '');

        // If not logged in or no preference, use one we selected earlier in the session.
        if ($palette === '') {
            $palette = Session::get('justlight-palette');
            $palette = is_string($palette) ? $palette : '';
        }

        // We haven't selected one this session? Use the site default
        if ($palette === '') {
            $palette = $this->getPreference('palette', 'justlight');
        }

        return $palette;
    }

    /**
     * Additional/updated translations.
     *
     * @param string $language
     *
     * @return array<string>
     */
    public function customTranslations(string $language): array
    {
        $file = $this->resourcesFolder() . 'lang/' . $language . '.php';

        return file_exists($file) ? (new Translation($file))->asArray() : [];
    }
};
















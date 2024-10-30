<?php

declare(strict_types=1);

namespace GatoGraphQLStandalone\GatoGraphQL\PluginSkeleton;

use GatoGraphQLStandalone\GatoGraphQL\Module;
use GatoGraphQLStandalone\GatoGraphQL\ModuleConfiguration;

use GatoGraphQL\GatoGraphQL\Facades\Registries\SettingsCategoryRegistryFacade;
use GatoGraphQL\GatoGraphQL\PluginSkeleton\AbstractGatoGraphQLBundleExtension;
use PoP\ComponentModel\App;
use function add_action;

abstract class AbstractStandaloneGatoGraphQLBundleExtension extends AbstractGatoGraphQLBundleExtension
{
    /**
     * Regenerate the configuration when the settings are updated
     *
     * @see wp-content/plugins/polylang/settings/settings-module.php
     */
    protected function doSetup(): void
    {
        parent::doSetup();

        $settingsCategories = $this->getInstallPluginSetupDataFormSettingsCategories();
        $this->maybeInstallPluginSetupDataWhenSettingsCategoriesOptionFormsUpdated($settingsCategories);
    }

    /**
     * @return string[]
     */
    protected function getInstallPluginSetupDataFormSettingsCategories(): array
    {
        return [];
    }

    /**
     * @param string[] $settingsCategories
     */
    protected function maybeInstallPluginSetupDataWhenSettingsCategoriesOptionFormsUpdated(array $settingsCategories): void
    {
        if ($settingsCategories === []) {
            return;
        }

        add_action(
            "update_option",
            function (string $optionName) use ($settingsCategories): void {
                $settingsCategoryRegistry = SettingsCategoryRegistryFacade::getInstance();
                $settingsCategoryOptionNames = array_map(
                    fn (string $settingsCategory) => $settingsCategoryRegistry->getSettingsCategoryResolver($settingsCategory)->getOptionsFormName($settingsCategory),
                    $settingsCategories
                );
                if (!in_array($optionName, $settingsCategoryOptionNames)) {
                    return;
                }

                if ($this->disableAutomaticConfigUpdates()) {
                    return;
                }
                
                $this->installPluginSetupDataWhenSettingsCategoriesOptionFormsUpdated();
            }
        );
    }
    
    protected function disableAutomaticConfigUpdates(): bool
    {
        /** @var ModuleConfiguration */
        $moduleConfiguration = App::getModule(Module::class)->getConfiguration();
        return $moduleConfiguration->disableAutomaticConfigUpdates();
    }

    protected function installPluginSetupDataWhenSettingsCategoriesOptionFormsUpdated(): void
    {
        $this->installPluginSetupData();
    }
}

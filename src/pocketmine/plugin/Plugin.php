<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

/**
 * Plugin related classes
 */

namespace pocketmine\plugin;

use pocketmine\Server;
use pocketmine\utils\Config;

/**
 * It is recommended to use PluginBase for the actual plugin
 */
interface Plugin
{
    /**
     * Called when the plugin is loaded, before calling onEnable()
     */
    public function onLoad(): void;

    /**
     * Called when the plugin is enabled
     */
    public function onEnable(): void;

    public function isEnabled(): bool;

    /**
     * Called when the plugin is disabled
     * Use this to free open things and finish actions
     */
    public function onDisable(): void;

    public function isDisabled(): bool;

    /**
     * Gets the plugin's data folder to save files and configuration
     */
    public function getDataFolder(): string;

    public function getDescription(): PluginDescription;

    /**
     * Gets an embedded resource in the plugin file.
     */
    public function getResource(string $filename);

    /**
     * Saves an embedded resource to its relative location in the data folder
     */
    public function saveResource(string $filename, bool $replace = false): bool;

    /**
     * Returns all the resources incrusted in the plugin
     */
    public function getResources();

    public function getConfig(): Config;

    public function saveConfig();

    public function saveDefaultConfig();

    public function reloadConfig();

    public function getServer(): Server;

    public function getName();

    public function getLogger(): PluginLogger;

    public function getPluginLoader(): PluginLoader;
}
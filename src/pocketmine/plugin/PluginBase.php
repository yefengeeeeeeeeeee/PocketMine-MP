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

namespace pocketmine\plugin;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Server;
use pocketmine\utils\Config;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

abstract class PluginBase implements Plugin, CommandExecutor
{
    private PluginLoader $loader;
    private Server $server;
    private bool $isEnabled = false;
    private bool $initialized = false;
    private PluginDescription $description;
    private string $dataFolder;
    private Config $config;
    private string $configFile;
    private string $file;
    private PluginLogger $logger;

    /**
     * Called when the plugin is loaded, before calling onEnable()
     */
    public function onLoad(): void
    {
    }

    public function onEnable(): void
    {
    }

    public function onDisable(): void
    {
    }

    /**
     * @return bool
     */
    public final function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public final function setEnabled(bool $boolean = true): void
    {
        if ($this->isEnabled !== $boolean) {
            $this->isEnabled = $boolean;
            if ($this->isEnabled === true) {
                $this->onEnable();
            } else {
                $this->onDisable();
            }
        }
    }

    /**
     * @return bool
     */
    public final function isDisabled(): bool
    {
        return $this->isEnabled === false;
    }

    public final function getDataFolder(): string
    {
        return $this->dataFolder;
    }

    public final function getDescription(): PluginDescription
    {
        return $this->description;
    }

    public final function init(PluginLoader $loader, Server $server, PluginDescription $description, $dataFolder, $file): void
    {
        if ($this->initialized === false) {
            $this->initialized = true;
            $this->loader = $loader;
            $this->server = $server;
            $this->description = $description;
            $this->dataFolder = rtrim($dataFolder, "\\/") . "/";
            $this->file = rtrim($file, "\\/") . "/";
            $this->configFile = $this->dataFolder . "config.yml";
            $this->logger = new PluginLogger($this);
        }
    }

    public function getLogger(): PluginLogger
    {
        return $this->logger;
    }

    public final function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function getCommand(string $name): Command|PluginIdentifiableCommand|null
    {
        $command = $this->getServer()->getPluginCommand($name);
        if ($command === null or $command->getPlugin() !== $this) {
            $command = $this->getServer()->getPluginCommand(strtolower($this->description->getName()) . ":" . $name);
        }

        if ($command instanceof PluginIdentifiableCommand and $command->getPlugin() === $this) {
            return $command;
        } else {
            return null;
        }
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, $label, array $args): bool
    {
        return false;
    }

    protected function isPhar(): bool
    {
        return str_starts_with($this->file, "phar://");
    }

    /**
     * Gets an embedded resource on the plugin file.
     * WARNING: You must close the resource given using fclose()
     *
     * @return resource Resource data, or null
     */
    public function getResource(string $filename)
    {
        $filename = rtrim(str_replace("\\", "/", $filename), "/");
        if (file_exists($this->file . "resources/" . $filename)) {
            return fopen($this->file . "resources/" . $filename, "rb");
        }

        return null;
    }

    public function saveResource(string $filename, bool $replace = false): bool
    {
        if (trim($filename) === "") {
            return false;
        }

        if (($resource = $this->getResource($filename)) === null) {
            return false;
        }

        $out = $this->dataFolder . $filename;
        if (!file_exists(dirname($out))) {
            mkdir(dirname($out), 0755, true);
        }

        if (file_exists($out) and $replace !== true) {
            return false;
        }

        $ret = stream_copy_to_stream($resource, $fp = fopen($out, "wb")) > 0;
        fclose($fp);
        fclose($resource);
        return $ret;
    }

    /**
     * Returns all the resources incrusted on the plugin
     *
     * @return string[]
     */
    public function getResources(): array
    {
        $resources = [];
        if (is_dir($this->file . "resources/")) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->file . "resources/")) as $resource) {
                $resources[] = $resource;
            }
        }

        return $resources;
    }

    public function getConfig(): Config
    {
        if (!isset($this->config)) {
            $this->reloadConfig();
        }

        return $this->config;
    }

    public function saveConfig(): void
    {
        if ($this->getConfig()->save() === false) {
            $this->getLogger()->critical("Could not save config to " . $this->configFile);
        }
    }

    public function saveDefaultConfig(): void
    {
        if (!file_exists($this->configFile)) {
            $this->saveResource("config.yml", false);
        }
    }

    public function reloadConfig(): void
    {
        $this->config = new Config($this->configFile);
        if (($configStream = $this->getResource("config.yml")) !== null) {
            $this->config->setDefaults(yaml_parse(config::fixYAMLIndexes(stream_get_contents($configStream))));
            fclose($configStream);
        }
    }

    public final function getServer(): Server
    {
        return $this->server;
    }

    public final function getName(): string
    {
        return $this->description->getName();
    }

    public final function getFullName(): string
    {
        return $this->description->getFullName();
    }

    protected function getFile(): string
    {
        return $this->file;
    }

    public function getPluginLoader(): PluginLoader
    {
        return $this->loader;
    }
}

<?php

/**
 * Copyright 2012-2022 Christoph M. Becker
 *
 * This file is part of Schedule_XH.
 *
 * Schedule_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Schedule_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Schedule_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Schedule;

final class InfoController
{
    /** @var string */
    private $pluginVersion;

    /** @var string */
    private $pluginFolder;

    /** @var string */
    private $dataFolder;

    /** @var View */
    private $view;

    public function __construct(string $pluginVersion, string $pluginFolder, string $dataFolder, View $view)
    {
        $this->pluginVersion = $pluginVersion;
        $this->pluginFolder = $pluginFolder;
        $this->dataFolder = $dataFolder;
        $this->view = $view;
    }

    public function execute(): void
    {
        echo $this->view->render("about", ["version" => $this->pluginVersion]),
            $this->systemCheck();
    }

    private function systemCheck(): string
    {
        $phpVersion = '7.1.0';
        $imgdir = "{$this->pluginFolder}/images/";
        $ok = '<img src="' . $imgdir . 'ok.png" alt="ok">';
        $warn = '<img src="' . $imgdir . 'warn.png" alt="warning">';
        $fail = '<img src="' . $imgdir . 'fail.png" alt="failure">';
        $o = '<h4>' . $this->view->text("syscheck_title") . '</h4>'
            . (version_compare(PHP_VERSION, $phpVersion) >= 0 ? $ok : $fail)
            . '&nbsp;&nbsp;' . $this->view->text("syscheck_phpversion", $phpVersion)
            . '<br>';
        foreach (['session'] as $ext) {
            $o .= (extension_loaded($ext) ? $ok : $fail)
                . '&nbsp;&nbsp;' . $this->view->text("syscheck_extension", $ext)
                . '<br>';
        }
        $o .= '<br>';
        foreach (['config/', 'css/', 'languages/'] as $folder) {
            $folders[] = "{$this->pluginFolder}/$folder";
        }
        $folders[] = $this->dataFolder;
        foreach ($folders as $folder) {
            $o .= (is_writable($folder) ? $ok : $warn)
                . '&nbsp;&nbsp;' . $this->view->text("syscheck_writable", $folder)
                . '<br>';
        }
        return $o;
    }
}

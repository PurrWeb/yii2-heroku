<?php

namespace purrweb\heroku;

use Yii;
use yii\console\Controller;

class HerokuGeneratorController extends Controller
{
    private function copyFile($root, $source, $target, &$all, $params)
    {
        if (!is_file($root . '/' . $source)) {
            echo "       skip $target ($source not exist)\n";
            return true;
        }
        if (is_file($root . '/' . $target)) {
            if (file_get_contents($root . '/' . $source) === file_get_contents($root . '/' . $target)) {
                echo "  unchanged $target\n";
                return true;
            }
            if ($all) {
                echo "  overwrite $target\n";
            } else {
                echo "      exist $target\n";
                echo "            ...overwrite? [Yes|No|All|Quit] ";


                $answer = !empty($params['overwrite']) ? $params['overwrite'] : trim(fgets(STDIN));
                if (!strncasecmp($answer, 'q', 1)) {
                    return false;
                } else {
                    if (!strncasecmp($answer, 'y', 1)) {
                        echo "  overwrite $target\n";
                    } else {
                        if (!strncasecmp($answer, 'a', 1)) {
                            echo "  overwrite $target\n";
                            $all = true;
                        } else {
                            echo "       skip $target\n";
                            return true;
                        }
                    }
                }
            }
            file_put_contents($root . '/' . $target, file_get_contents($root . '/' . $source));
            return true;
        }
        echo "   generate $target\n";
        @mkdir(dirname($root . '/' . $target), 0777, true);
        file_put_contents($root . '/' . $target, file_get_contents($root . '/' . $source));
        return true;
    }

    private function getFileList($root, $basePath = '')
    {
        $files = [];
        $handle = opendir($root);
        while (($path = readdir($handle)) !== false) {
            if ($path === '.svn' || $path === '.' || $path === '..') {
                continue;
            }
            $fullPath = "$root/$path";
            $relativePath = $basePath === '' ? $path : "$basePath/$path";
            if (is_dir($fullPath)) {
                $files = array_merge($files, $this->getFileList($fullPath, $relativePath));
            } else {
                $files[] = $relativePath;
            }
        }
        closedir($handle);
        return $files;
    }

    public function actionIndex()
    {
        $vendorPath = 'vendor' . explode('vendor', str_replace('\\', '/', __DIR__))[1];
        $root = str_replace('\\', '/', dirname(Yii::getAlias('@app')));
        $files = $this->getFileList(str_replace('\\', '/', __DIR__) . '/templates/environments');
        $all = false;
        foreach ($files as $file) {
            if (!$this->copyFile($root, $vendorPath . '/templates/environments/' . $file, 'environments/' . $file, $all, [])) {
                break;
            };
        }

        $files = $this->getFileList(str_replace('\\', '/', __DIR__) . '/templates/approot');
        foreach ($files as $file) {
            if (!$this->copyFile($root, $vendorPath . '/templates/approot/' . $file, $file, $all, [])) {
                break;
            };
        }
    }
}

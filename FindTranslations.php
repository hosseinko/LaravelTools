<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FindTranslations extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:find-translations {--create-file=no}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds strings that are needed to be translated';

    private $strings = [];
    private $tmp     = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        try {
            $createTranslationFiles = $this->option('create-file');

            $path = app_path();
            $dirs = $this->listDirs($path);
            $this->parseDirs($path, $dirs);

            $path = resource_path('views');
            $dirs = $this->listDirs($path);
            $this->parseDirs($path, $dirs);


            if ($createTranslationFiles == 'yes') {
                $this->createTranslationFile();
            }
            $this->outputResult();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    private function listDirs($path) {
        $tmpDirs = scandir($path);
        $dirs = [];
        foreach ($tmpDirs as $dir) {
            if (!in_array($dir, ['.', '..'])) {
                $dirs[] = $dir;
            }
        }

        return $dirs;
    }

    private function parseDirs($path, $dirs) {
        foreach ($dirs as $dir) {
            if (is_dir($path . '/' . $dir)) {
                $this->parseDirs($path . '/' . $dir, $this->listDirs($path . '/' . $dir));
            } else {
                $this->parseFile($path . '/' . $dir);
            }

        }
    }

    private function parseFile($filename) {
        $fileContent = file_get_contents($filename);
        preg_match_all('/(?:trans|\_\_|\@lang)\(([a-z0-9\.\_\'\"]+)\)/i', $fileContent, $matches);

        if (is_array($matches) && $matches > 0) {
            foreach ($matches[1] as $match) {
                $exMatch = explode('.', str_replace("'", '', $match), 2);
                if (isset($this->tmp[$exMatch[0]])) {
                    if (!in_array($exMatch[1], $this->tmp[$exMatch[0]])) {
                        $this->strings[$exMatch[0]][][0] = $exMatch[1];
                        $this->tmp[$exMatch[0]][] = $exMatch[1];
                    }
                } else {
                    $this->strings[$exMatch[0]][][0] = $exMatch[1];
                    $this->tmp[$exMatch[0]][] = $exMatch[1];
                }
            }
        }
    }

    private function createTranslationFile() {
        if (is_array($this->strings) && count($this->strings) > 0) {
            $path = resource_path('lang');

            $dirs = $this->listDirs($path);

            foreach ($this->strings as $key => $values) {
                foreach ($dirs as $dir) {
                    $filename = $path . '/' . $dir . '/' . $key . '.php';
                    $this->updateFileContents($filename, $values);
                }
            }
        } else {
            $this->error('Nothing found');
        }
    }

    private function updateFileContents($filename, $data) {
        if (file_exists($filename)) {
            $fileContents = require_once $filename;
            $rawFilename = str_replace('.php', '', basename($filename));

            if (is_array($fileContents)) {
                $newData = [];
                foreach ($this->tmp[$rawFilename] as $value) {
                    if (!array_key_exists($value, $fileContents)) {
                        $newData[][0] = $value . '| ';
                    } else {
                        $newData[][0] = $value . '|' . $fileContents[$value];
                    }
                }
                file_put_contents($filename, $this->arrayToFileContent($newData));
            } else {
                file_put_contents($filename, $this->arrayToFileContent($data));
            }
        } else {
            file_put_contents($filename, $this->arrayToFileContent($data));
        }
    }

    private function arrayToFileContent($values) {
        $data = '<?php' . chr(10);
        $data .= 'return [' . chr(10);
        foreach ($values as $value) {
            $explodedData = explode('|', $value[0]);
            if (is_array($explodedData) && count($explodedData) > 1) {
                $data .= str_repeat(' ', 4) . "'" . $explodedData[0] . "' => '" . trim($explodedData[1]) . "'," . chr(10);
            } else {
                $data .= str_repeat(' ', 4) . "'" . $value[0] . "' => ''," . chr(10);
            }
        }
        $data .= '];';

        return $data;
    }

    private function outputResult() {
        if (is_array($this->strings) && count($this->strings) > 0) {
            foreach ($this->strings as $key => $values) {
                $this->table([ucfirst($key)], $this->strings[$key]);
            }
        } else {
            $this->error('Nothing found');
        }
    }
}

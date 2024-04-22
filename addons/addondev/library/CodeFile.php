<?php

namespace addons\addondev\library;

class CodeFile
{

    /**
     * The code file is new.
     */
    const OP_CREATE = 'create';

    /**
     * The code file already exists, and the new one may need to overwrite it.
     */
    const OP_OVERWRITE = 'overwrite';

    /**
     * The new code file and the existing one are identical.
     */
    const OP_SKIP = 'skip';

    /**
     * delete file
     */
    const OP_DELETE = 'delete';

    /**
     *
     * @var string an ID that uniquely identifies this code file.
     */
    public $id;

    /**
     *
     * @var string the file path that the new code should be saved to.
     */
    public $path;

    public $shortPath;

    /**
     * addon name
     *
     * @var string
     */
    public $addonName;

    /**
     *
     * @var string the newly generated code content
     */
    public $content;

    /**
     *
     * @var string the operation to be performed. This can be [[OP_CREATE]], [[OP_OVERWRITE]] or [[OP_SKIP]].
     */
    public $operation;

    /**
     * 
     * @var string name of file extension
     */
    public $ext;

    /**
     * Constructor.
     *
     * @param string $path
     *            the file path that the new code should be saved to.
     * @param string $content
     *            the newly generated code content.
     * @param array $genAnnouncement 生成声明
     */
    public function __construct($addonName, $path, $content = NULL, $genAnnouncement = true)
    {
        $this->addonName = $addonName;
        $this->path = strtr($path, '/\\', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR);
        $this->content = trim($content);
        $this->id = md5($this->path);
        $this->shortPath = str_replace(ROOT_PATH, '', $this->path);
        $this->ext = $this->getExtension($this->shortPath);
        if ($this->content && $genAnnouncement) {
            $announcement = get_addon_config('addondev')['announcement'];
            $holders = [
                '{addonName}' => strtoupper($this->addonName),
                '{year}' => date('Y'),
                '{date}' => date('Y-m-d'),
                '{datetime}' => date('Y-m-d H:i:s'),
            ];
            $addonInfo = get_addon_info($addonName);
            foreach ($addonInfo as $name => $val) {
                $holders['{' . $name . '}'] = $val;
            }
            $announcement = strtr($announcement, $holders);
            if ($this->ext == 'php' && $announcement) {
                $replace = "<?php\n";
                $replace .= $announcement;
                $this->content = str_replace('<?php', $replace, $this->content);
            }
        }


        if (is_file($path)) {
            if ($content === null) {
                $this->operation = self::OP_DELETE;
            } else {
                $this->operation = trim(file_get_contents($path)) === $this->content ? self::OP_SKIP : self::OP_OVERWRITE;
            }
        } else {
            if ($content === null) {
                $this->operation = self::OP_SKIP;
            } else {
                $this->operation = self::OP_CREATE;
            }
        }
    }

    /**
     * Saves the code into the file specified by [[path]].
     *
     * @return string|bool the error occurred while saving the code file, or true if no error.
     */
    public function save()
    {
        if ($this->operation === self::OP_CREATE) {
            $dir = dirname($this->path);
            if (!is_dir($dir)) {
                $mask = @umask(0);
                $result = @mkdir($dir, 0775, true);
                @umask($mask);
                if (!$result) {
                    return "Unable to create the directory '$dir'.";
                }
            }
        }

        if (@file_put_contents($this->path, $this->content) === false) {
            return "Unable to write the file '{$this->path}'.";
        }

        $mask = @umask(0);
        @chmod($this->path, 0775);
        @umask($mask);

        return true;
    }

    public function isController(){
        if(stripos($this->shortPath,'\\controller\\')===false){
            return false;
        }
        return true;
    }

    public function delete(){
        if ($this->operation === self::OP_DELETE) {
            $addonPath = 'addons' . DS . $this->addonName . DS;
            $start = strlen($addonPath);
            $exeFile = ROOT_PATH . substr($this->shortPath,$start);
            @unlink($exeFile);
            @unlink($this->path);
        }
    }

    /**
     *
     * @return string the code file path relative to the application base path.
     */
    public function getRelativePath()
    {
        if (strpos($this->path, ADDON_PATH) === 0) {
            return substr($this->path, strlen(ADDON_PATH) + 1);
        }

        return $this->path;
    }

    /**
     *
     * @return string the code file extension (e.g. php, txt)
     */
    public function getType()
    {
        if (($pos = strrpos($this->path, '.')) !== false) {
            return substr($this->path, $pos + 1);
        }

        return 'unknown';
    }

    /**
     * Returns preview or false if it cannot be rendered
     *
     * @return bool|string
     */
    public function preview()
    {
        if (($pos = strrpos($this->path, '.')) !== false) {
            $type = substr($this->path, $pos + 1);
        } else {
            $type = 'unknown';
        }


        if (empty($this->content)) {
            if (file_exists($this->path))
                $this->content = file_get_contents($this->path);
        }

        if ($type === 'php') {
            return highlight_string($this->content, true);
        } elseif (!in_array($type, [
            'jpg',
            'gif',
            'png',
            'exe'
        ])) {
            return nl2br(htmlspecialchars($this->content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', true));
        }

        return false;
    }

    function getExtension($file)
    {
        return substr(strrchr($file, '.'), 1);
    }

    /**
     * Returns diff or false if it cannot be calculated
     *
     * @return bool|string
     */
    public function diff()
    {
        $type = strtolower($this->getType());
        if (in_array($type, [
            'jpg',
            'gif',
            'png',
            'exe'
        ])) {
            return false;
        } elseif ($this->operation === self::OP_OVERWRITE) {
            return $this->renderDiff(trim(file_get_contents($this->path)), $this->content);
        }

        return '';
    }

    /**
     * Renders diff between two sets of lines
     *
     * @param mixed $lines1
     * @param mixed $lines2
     * @return string
     */
    private function renderDiff($lines1, $lines2)
    {
        if (!is_array($lines1)) {
            $lines1 = explode("\n", $lines1);
        }
        if (!is_array($lines2)) {
            $lines2 = explode("\n", $lines2);
        }
        foreach ($lines1 as $i => $line) {
            $lines1[$i] = rtrim($line, "\r\n");
        }
        foreach ($lines2 as $i => $line) {
            $lines2[$i] = rtrim($line, "\r\n");
        }

        $renderer = new DiffRendererHtmlInline();

        $diff = new \Diff($lines1, $lines2);

        return $diff->render($renderer);
    }
}

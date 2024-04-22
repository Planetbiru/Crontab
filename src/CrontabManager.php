<?php

namespace Crontab;

use Crontab\Exception\InvalidServerException;
use Exception;
use InvalidArgumentException;
use Net_SSH2_LibSSH2;

class CrontabManager
{
    /**
     * Username
     *
     * @var string
     */
    private $username;
    /**
     * Password
     *
     * @var string
     */
    private $password;
    /**
     * Path
     *
     * @var string
     */
    private $path;
    /**
     * Handle
     *
     * @var string
     */
    private $handle;
    /**
     * Net_SSH2_LibSSH2
     *
     * @var Net_SSH2_LibSSH2
     */
    private $ssh;
    
    /**
     * Cron file
     *
     * @var string
     */
    private $cronFile;
    
    /**
     * Output
     *
     * @var string
     */
    private $output = "";
    
    /**
     * Return
     *
     * @var string
     */
    private $return = "";

    public function __construct($host, $port, $username, $password)
    {
        $path_length     = strrpos(__FILE__, "/");
        $this->path      = substr(__FILE__, 0, $path_length) . '/';
        $this->handle    = 'crontab.txt';
        $this->cronFile = "{$this->path}{$this->handle}";
        $this->username = $username;
        $this->password = $password;
        echo "AAAAAAAA ";
        print_r($this->ssh);
        try {
            if (is_null($host) || is_null($port) || is_null($username) || is_null($password)) {
                throw new InvalidServerException("Please specify the host, port, username and password!");
            }
            $this->ssh = new Net_SSH2_LibSSH2($host, $port);
            
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->errorMessage($e->getMessage());
        }
    }

    public function exec()
    {
        $argument_count = func_num_args();
        try {
            if (!$argument_count) {
                throw new InvalidArgumentException("There is nothing to execute, no arguments specified.");
            }
            $arguments = func_get_args();
            $command_string = ($argument_count > 1) ? implode(" && ", $arguments) : $arguments[0];
            $command_string .= "\r\n";
            $options = array(
                'command'=>$command_string,
                'login_name'=>$this->username,
                'password'=>$this->password
            );
            $this->ssh->sshExec($output, $return, $options);
            $this->output = $output;
            $this->return = $return;
        } catch (Exception $e) {
            $this->errorMessage($e->getMessage());
        }
        return $this;
    }

    public function writeToFile($path = null, $handle = null)
    {
        if (!$this->crontab_file_exists()) {
            $this->handle = (is_null($handle)) ? $this->handle : $handle;
            $this->path   = (is_null($path))   ? $this->path   : $path;
            $this->cronFile = "{$this->path}{$this->handle}";
            $init_cron = "crontab -l > {$this->cronFile} && [ -f {$this->cronFile} ] || > {$this->cronFile}";
            $this->exec($init_cron);
        }
        return $this;
    }
    public function removeFile()
    {
        if ($this->crontab_file_exists()) {
            $this->exec("rm -rf {$this->cronFile}");
        }
        return $this;
    }
    public function append_cronjob($cron_jobs = null)
    {
        if (is_null($cron_jobs)) 
        {
            $this->errorMessage("Nothing to append!  Please specify a cron job or an array of cron jobs.");
        }
        $append_cronfile = "echo '";
        $append_cronfile .= (is_array($cron_jobs)) ? implode("\n", $cron_jobs) : $cron_jobs;
        $append_cronfile .= "'  >> {$this->cronFile}";
        $install_cron = "crontab {$this->cronFile}";
        $this->writeToFile()->exec($append_cronfile, $install_cron)->removeFile();
        return $this;
    }
    public function remove_cronjob($cron_jobs = null)
    {
        if (is_null($cron_jobs)) $this->errorMessage("Nothing to remove!  Please specify a cron job or an array of cron jobs.");
        $this->writeToFile();
        $cron_array = file($this->cronFile, FILE_IGNORE_NEW_LINES);
        if (empty($cron_array)) $this->errorMessage("Nothing to remove!  The cronTab is already empty.");
        $original_count = count($cron_array);
        if (is_array($cron_jobs)) {
            foreach ($cron_jobs as $cron_regex) $cron_array = preg_grep($cron_regex, $cron_array, PREG_GREP_INVERT);
        } else {
            $cron_array = preg_grep($cron_jobs, $cron_array, PREG_GREP_INVERT);
        }
        return ($original_count === count($cron_array)) ? $this->removeFile() : $this->remove_crontab()->append_cronjob($cron_array);
    }
    public function remove_crontab()
    {
        $this->exec("crontab -r")->removeFile();
        return $this;
    }
    private function crontab_file_exists()
    {
        return file_exists($this->cronFile);
    }
    private function errorMessage($error) //NOSONAR
    {
        // do nothing
    }
    

    /**
     * Get output
     *
     * @return  string
     */ 
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Get return
     *
     * @return  string
     */ 
    public function getReturn()
    {
        return $this->return;
    }
}

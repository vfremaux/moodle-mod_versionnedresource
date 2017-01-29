<?php

class test_client {

    protected $t; // target.

    public function __construct() {

        $this->t = new StdClass;

        // Setup this settings for tests
        $this->t->baseurl = ''; // The remote Moodle url to push in.
        $this->t->wstoken = ''; // the service token for access.
        $this->t->filepath = ''; // Some physical location on your system.
        $this->t->idnumber = ''; // ID Number of the VR test instance.

        $this->t->uploadservice = '/webservice/upload.php';
        $this->t->service = '/webservice/rest/server.php';
    }

    public function test_get_version_info() {

        if (empty($this->t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => $this->t->wstoken,
                        'wsfunction' => 'mod_versionnedresource_get_version_info',
                        'moodlewsrestformat' => 'json',
                        'vridsource' => 'idnumber',
                        'vrid' => $this->t->idnumber);

        $serviceurl = $this->t->baseurl.$this->t->service;

        return $this->send($serviceurl, $params);
    }

    public function test_get_version_file_url() {
        $versions = $this->test_get_version_info();

        $serviceurl = $this->t->baseurl.$this->t->service;

        $params = array('wstoken' => $this->t->wstoken,
                        'wsfunction' => 'mod_versionnedresource_get_version_file_url',
                        'moodlewsrestformat' => 'json',
                        'versionid' => '');

        return $this->send($serviceurl, $params);
    }

    public function test_get_last_branch_file_url() {
        $versions = $this->test_get_version_info();

        $serviceurl = $this->t->baseurl.$this->t->service;

        $params = array('wstoken' => $this->t->wstoken,
                        'wsfunction' => 'mod_versionnedresource_get_version_file_url',
                        'moodlewsrestformat' => 'json',
                        'vridsource' => 'idnumber',
                        'vrid' => $this->t->idnumber,
                        'branch' => 'BRANCH_TEST');

        return $this->send($serviceurl, $params);
    }

    public function test_version_push() {


        $version = new StdClass();
        $version->branch = 'BRANCH_TEST';
        $version->version = 'VERSION_TEST';
        $version->maturity = 'MATURITY_TEST';
        $version->visible = 0;
        $version->changes = 'Version has changes';

        echo "Publishing to test target\n";

        // Perfom the test publication.
        if ($filerec = $this->upload_file($this->t, $this->t->filepath)) {

            // Commit version.
            $resource = $this->commit_file($this->t, $filerec->itemid, $this->t->idnumber, $version);

            print_r($resource);
        } else {
            echo "Could not upload file to target\n";
        }
    }

    protected function upload_file($target, $file) {

        if (empty($target->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $serviceurl = $target->baseurl.$target->uploadservice;

        $params = array('token' => $target->wstoken,
                        'itemid' => 0,
                        'filearea' => 'draft');

        $ch = curl_init($uploadurl);

        $curlfile = new CURLFile($file, 'x-application/zip', 'resourcefile');
        $params['resourcefile'] = $curlfile;

        return $this->send($serviceurl, $params);
    }

    protected function commit_file($target, $draftitemid, $idnumber, $jsoninfo) {

        if (empty($target->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => $target->wstoken,
                        'wsfunction' => 'mod_versionnedresource_commit_version',
                        'moodlewsrestformat' => 'json',
                        'vridsource' => 'idnumber',
                        'vrid' => $idnumber,
                        'draftitemid' => $draftitemid,
                        'jsoninfo' => json_encode($jsoninfo));

        $serviceurl = $target->baseurl.$target->commitservice;

        return $this->send($serviceurl, $params);
    }

    protected function send($serviceurl, $params) {
        $ch = curl_init($serviceurl);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        echo "Firing CUrl $serviceurl ... \n";
        if (!$result = curl_exec($ch)) {
            echo "CURL Error : ".curl_errno($ch).' '.curl_error($ch)."\n";
            return;
        }

        echo $result;
        if (preg_match('/EXCEPTION/', $result)) {
            echo $result;
            return;
        }

        $result = json_decode($result);
        print_r($result);
        return $result;
    }
}

// Effective test scénario

$client = new test_client();

$client->test_get_version_info(); // Test empty resource.
$client->test_version_push();
$client->test_version_info();
$client->test_get_version_file_url();
$client->test_version_push();
$client->test_get_last_branch_file_url();

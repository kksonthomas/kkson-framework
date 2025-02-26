<?php


namespace KKsonFramework\App;

use KKsonFramework\Auth\Auth;
use KKsonFramework\CRUD\SlimKKsonCRUD;
use League\Plates\Engine;
use Slim\Http\Request;
use Slim\Slim;
use Stringy\Stringy;
use KKsonFramework\Utils\UrlUtils;

class FrontendApp extends App
{

    private static $instance;
    protected $slim;
    protected $templateEngine;
    /**
     * @var string
     */
    private $bodyEndHTML;
    /**
     * @var string
     */
    private $headHTML;

    protected $allowExt = [
        "jpg", "jpeg", "gif", "png", "apng", "svg", "pdf", "doc", "docx", "ppt", "pptx", "xls", "xlsx", "mp4"
    ];


    public static function init($slim = null)
    {
        if(parent::init()) {
            self::$instance = new self($slim);
            return self::$instance;
        } else {
            return null;
        }
    }

    public static function setCrud(SlimKKsonCRUD $crud) {
        throw new \Exception("Curd is not allowed in frontend app");
    }

    /**
     * @return FrontendApp
     */
    public static function getInstance()
    {
        return static::$instance;
    }

    public function __construct($slim = null)
    {
        if($slim) {
            $this->slim = $slim;
        } else {
            $this->slim = new Slim();
        }
        $this->templateEngine = new Engine("view");

        if(session_id() == '') {
            session_start();
        }
    }

    public function checkLogin() {
        $app = $this;
        Auth::checkLogin(function () use ($app) {
            // Get request object
            /** @var Request $req */
            $req = $app->getSlim()->request;

            //Get root URI
            $rootUri = $req->getRootUri();

            //Get resource URI
            $resourceUri = $req->getResourceUri();

            $_SESSION["redirect"] = $resourceUri;
            $path = "/auth/login";
            $app->getSlim()->redirect(UrlUtils::url($path, true));
        });
    }

    /**
     * @return Slim
     */
    public function getSlim()
    {
        return $this->slim;
    }

    /**
     * @param mixed $slim
     */
    public function setSlim($slim): void
    {
        $this->slim = $slim;
    }

    /**
     * @return Engine
     */
    public function getTemplateEngine()
    {
        return $this->templateEngine;
    }

    public function render($name, $data = [], $prefix = "frontend/") {
        return $this->getTemplateEngine()->render($prefix.$name, array_merge([
            "app" => $this
        ], $data));
    }

    /**
     * @param $url
     */
    public function addBodyEndExternalJs($url) {
        $this->bodyEndHTML .= "<script src='$url'></script>";
    }

    /**
     * @param $script
     */
    public function addJavaScriptCode($script) {
        $this->bodyEndHTML .= "<script>$script</script>";
    }

    public function addBodyEndHTML($html) {
        $this->bodyEndHTML .= $html;
    }

    public function getBodyEndHTML() {
        return $this->bodyEndHTML;
    }

    /**
     * @param $css
     */
    public function addStyle($css) {
        $this->headHTML .= "<style>$css</style>";
    }

    public function addHeadExternalCss($url) {
        $this->headHTML .= "<link rel=\"stylesheet\" href='$url'/>";
    }
    /**
     * @param $html
     */
    public function addHeadHTML($html) {
        $this->headHTML .= $html;
    }

    /**
     * @return string
     */
    public function getHeadHTML() {
        return $this->headHTML;
    }

    public function upload($fieldName = "upload", $folder = null) {
        try {
            if ($folder == null) {
                $folder = "upload" . DIRECTORY_SEPARATOR;
            }

            if (isset($_FILES[$fieldName])) {

                $filenameArray = explode(".", $_FILES[$fieldName]["name"]);

                if (count($filenameArray) >=2) {
                    $ext = $filenameArray[count($filenameArray) - 1];
                } else {
                    $ext = "";
                }

                if (! in_array($ext, $this->allowExt)) {
                    throw new \Exception("Format is not allowed.");
                }

                $filename = dechex(rand(1, 99999999)) . "-" . time() . "." . $ext;

                $relativePath = $folder . $filename;
                /**TODO make callback before move uploaded file**/
//                if(ClamAV::isDaemonRunning()) {
//                    ClamAV::checkUploadedFile($_FILES[$fieldName]);
//                } else {
//                    throw new \Exception("Anti-Virus software is not running. Please contact system administrator");
//                }
                $dir = dirname($relativePath);
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                move_uploaded_file($_FILES[$fieldName]["tmp_name"], $relativePath);

                $mime = mime_content_type($relativePath);
                $output = [
                    "fileName" =>$filename,
                    "uploaded" => 1,
                    "url" => $relativePath,
                    "mime" => $mime,
                    "status" => "SUCC"
                ];
            } else {
                throw new \Exception("The file is too big.");
            }
        } catch (\Exception $ex) {
            $output = [
                "fileName" => "",
                "uploaded" => 0,
                "url" => "",
                "status" => "FAIL",
                "error" => [
                    "message" => $ex->getMessage()
                ]
            ];
        }

        return $output;
    }

}
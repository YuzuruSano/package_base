<?php
namespace Concrete\Package\PackageBase;
defined('C5_EXECUTE') or die("Access Denied.");

use Package;
use Concrete\Package\SuitonBaseUtil\Src\AdditionalUtil\AdditionalUtilServiceProvider;
use BlockType;
use Loader;
use Concrete\Core\Page\Single as SinglePage;
use Core;
use Config;
use User;
use Page;
use UserInfo;
use Exception;
use Concrete\Core\Block\BlockController;
use Route;
use Router;
use Database;
use Concrete\Core\Page\Single;
use Concrete\Core\Page\Theme\Theme;
use Concrete\Core\Foundation\ClassAliasList;

class Controller extends Package
{

	protected $pkgHandle = 'package_base';
	protected $appVersionRequired = '5.7.4';
	protected $pkgVersion = '0.2';
	protected $pkgAutoloaderMapCoreExtensions = true;

	public function getPackageDescription()
	{
		return "パッケージ作りの雛形 いろいろ試す用";
	}
	public function getPackageName()
	{
		return "パッケージ作り処理に関するいろいろ";
	}

	/* ===============================================
	インストールされているパッケージをconcrete5がロードする際に呼び出される
	=============================================== */
	 public function on_start()
	{
		/* ===============================================
		アセットの登録
		=============================================== */
		// $al = \Concrete\Core\Asset\AssetList::getInstance();
		// $al->register('css', 'xxx_css', 'css/xxx.css', array(), $this);
		// $al->register('javascript', 'xxx_js', 'js/xxx.js', array(), $this);

		// $al->registerGroup('my_register_group', array(
		//     array('javascript', 'underscore');//sample:Call core JS direct
		//     array('javascript', 'xxx_js'),
		//     array('css', 'xxx_css')
		// ));

		/* ===============================================
		configへデータ登録
		=============================================== */
		/*
		Define test or deploy
		Please change host to suit your environment
		----------------------- */
		// $config_param = array(
		// 	'seo.title_format' => '%2$s | %1$s',
		// 	'external.news_overlay' => false,
		// 	'myenv' => $env,
		// 	'normal_url' => $normal,
		// 	'secure_url' => $secure,
		// );
		// $this->setMyConfig($config_param);//setMyConfigはConfig::set()の拡張

		/* ===============================================
		ヘルパーの追加サンプル
		'packagse_base/src/AdditionalUtil/Service/AdditionalUtil.php'ないの処理をヘルパーとして利用できるようにする

		Thanks!! http://www.concrete5.org/community/forums/5-7-discussion/helpers/
		=============================================== */
		// $app = Core::getFacadeApplication();
		// $sp = new AdditionalUtilServiceProvider($app);
		// $sp->register();

		/* ===============================================
		コアクラスのオーバーライドサンプル
		=============================================== */
		//$listにはconcrete/app.phpのCore Aliasesが返ってくる
		//$list = ClassAliasList::getInstance();
		//$list->register('aliases key', '\Concrete\Package\package_base\〜以下concrete/app.phpのCore Aliasesを参考に');
		//エディターの設定を上書きしたり
		// Core::bindShared('editor', function() {
		// 	return new \Concrete\Package\SuitonBaseUtil\Src\Editor\RedactorEditor();
		// });
	}

	public function install(){
		// Run default install process
		$pkg = parent::install();
		$db = Database::getActiveConnection();

		/* ===============================================
		テーマのインストール
		=============================================== */
		//Theme::add('base_theme', $pkg);

		/* ===============================================
		ブロックのインストール
		=============================================== */
		//BlockType::installBlockTypeFromPackage('block_handle', $pkg);

		/* ===============================================
		シングルページの追加 または更新
		※引数については後述のaddSinglePageを参照
		=============================================== */
		//$this->addSinglePage('/dashboard/system/basics/name', 'Name', 'test');
	}

	/* ===============================================
	パッケージのアップグレード時の処理
	=============================================== */
	public function upgrade()
	{
		$pkg = $this->getByID($this->getPackageID());
		parent::upgrade();
		$db = Database::getActiveConnection();

		//シングルページの更新
		//$this->addSinglePage('/dashboard/system/basics/name', 'Name', 'test');
	}
	/* ===============================================
	パッケージのアンイストール時の処理
	=============================================== */
	public function uninstall() {
		parent::uninstall();
		$db = Loader::db();
	}

	/**
	* Adds/installs or refresh a single page
	* @param string $pagePath The relative path to the single page
	* @param string $pageName The name of the single page
	* @param string $description The description for the single page
	* Thanks! http://www.code-examples.com/concrete-5-7-creating-a-single-page-for-the-frontend/
	*/
	private function addSinglePage($pagePath, $pageName, $description){
		$pkg = Package::getByHandle($this->getPackageHandle());
		$singlePage = Page::getByPath($pagePath);

		if ($singlePage->isError() && $singlePage->getError() == COLLECTION_NOT_FOUND) {
			/* @var $singlePage Single*/
			$singlePage = Single::add($pagePath, $pkg);
			$singlePage->update(array('cName' => $pageName, 'cDescription' => $description));

			return $singlePage;
		}else{//refresh single page by package file
			$cID = Page::getByPath($pagePath)->getCollectionID();
			Loader::db()->execute('update Pages set pkgID = ? where cID = ?', array($pkg->pkgID, $cID));
		}

		return null;
	}

	/**
	* Set congfig
	* Thanks! https://gist.github.com/hissy/d11551ad58e5f3d0fcd4
	*/
	private function setMyConfig($param = null){
		if($param){
			foreach($param as $key => $val)
			Config::set('concrete.'.$key, $val);
		}
	}
}

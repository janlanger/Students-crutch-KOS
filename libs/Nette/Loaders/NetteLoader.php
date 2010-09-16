<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Loaders
 */



/**
 * Nette auto loader is responsible for loading Nette classes and interfaces.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Loaders
 */
class NNetteLoader extends NAutoLoader
{
	/** @var NNetteLoader */
	private static $instance;

	/** @var array */
	public $list = array(
		'iannotation' => '/Reflection/IAnnotation.php',
		'iauthenticator' => '/Security/IAuthenticator.php',
		'iauthorizator' => '/Security/IAuthorizator.php',
		'icachejournal' => '/Caching/ICacheJournal.php',
		'icachestorage' => '/Caching/ICacheStorage.php',
		'icomponent' => '/ComponentModel/IComponent.php',
		'icomponentcontainer' => '/ComponentModel/IComponentContainer.php',
		'iconfigadapter' => '/Config/IConfigAdapter.php',
		'idebugpanel' => '/Debug/IDebugPanel.php',
		'ifiletemplate' => '/Templates/IFileTemplate.php',
		'itemplate' => '/Templates/ITemplate.php',
		'iformcontrol' => '/Forms/IFormControl.php',
		'iformrenderer' => '/Forms/IFormRenderer.php',
		'ihttprequest' => '/Web/IHttpRequest.php',
		'ihttpresponse' => '/Web/IHttpResponse.php',
		'iidentity' => '/Security/IIdentity.php',
		'imailer' => '/Mail/IMailer.php',
		'irenderable' => '/Application/IRenderable.php',
		'ipartiallyrenderable' => '/Application/IRenderable.php',
		'ipresenter' => '/Application/IPresenter.php',
		'ipresenterloader' => '/Application/IPresenterLoader.php',
		'ipresenterresponse' => '/Application/IPresenterResponse.php',
		'iresource' => '/Security/IResource.php',
		'irole' => '/Security/IRole.php',
		'irouter' => '/Application/IRouter.php',
		'iservicelocator' => '/Environment/IServiceLocator.php',
		'isignalreceiver' => '/Application/ISignalReceiver.php',
		'istatepersistent' => '/Application/IStatePersistent.php',
		'isubmittercontrol' => '/Forms/ISubmitterControl.php',
		'itranslator' => '/Utils/ITranslator.php',
		'iuser' => '/Web/IUser.php',
		'argumentoutofrangeexception' => '/Utils/exceptions.php',
		'invalidstateexception' => '/Utils/exceptions.php',
		'notimplementedexception' => '/Utils/exceptions.php',
		'notsupportedexception' => '/Utils/exceptions.php',
		'deprecatedexception' => '/Utils/exceptions.php',
		'memberaccessexception' => '/Utils/exceptions.php',
		'ioexception' => '/Utils/exceptions.php',
		'filenotfoundexception' => '/Utils/exceptions.php',
		'directorynotfoundexception' => '/Utils/exceptions.php',
		'fatalerrorexception' => '/Utils/exceptions.php',
		'datetime53' => '/Utils/DateTime53.php',
		'nabortexception' => '/Application/Exceptions/AbortException.php',
		'nambiguousserviceexception' => '/Environment/ServiceLocator.php',
		'nobject' => '/Utils/Object.php',
		'nfreezableobject' => '/Utils/FreezableObject.php',
		'nservicelocator' => '/Environment/ServiceLocator.php',
		'nannotation' => '/Reflection/Annotation.php',
		'nannotationsparser' => '/Reflection/AnnotationsParser.php',
		'ncomponent' => '/ComponentModel/Component.php',
		'ncomponentcontainer' => '/ComponentModel/ComponentContainer.php',
		'nrecursivecomponentiterator' => '/ComponentModel/ComponentContainer.php',
		'nformcontainer' => '/Forms/FormContainer.php',
		'nform' => '/Forms/Form.php',
		'nappform' => '/Application/AppForm.php',
		'napplication' => '/Application/Application.php',
		'napplicationexception' => '/Application/Exceptions/ApplicationException.php',
		'narraylist' => '/Utils/ArrayList.php',
		'narraytools' => '/Utils/ArrayTools.php',
		'nauthenticationexception' => '/Security/AuthenticationException.php',
		'nautoloader' => '/Loaders/AutoLoader.php',
		'nbadrequestexception' => '/Application/Exceptions/BadRequestException.php',
		'nbadsignalexception' => '/Application/Exceptions/BadSignalException.php',
		'nbasetemplate' => '/Templates/BaseTemplate.php',
		'nformcontrol' => '/Forms/Controls/FormControl.php',
		'nbutton' => '/Forms/Controls/Button.php',
		'ncache' => '/Caching/Cache.php',
		'ncachinghelper' => '/Templates/Filters/CachingHelper.php',
		'ncallback' => '/Utils/Callback.php',
		'ncheckbox' => '/Forms/Controls/Checkbox.php',
		'nclassreflection' => '/Reflection/ClassReflection.php',
		'nclirouter' => '/Application/Routers/CliRouter.php',
		'nconfig' => '/Config/Config.php',
		'nconfigadapterini' => '/Config/ConfigAdapterIni.php',
		'nconfigurator' => '/Environment/Configurator.php',
		'npresentercomponent' => '/Application/PresenterComponent.php',
		'ncontrol' => '/Application/Control.php',
		'nconventionalrenderer' => '/Forms/Renderers/ConventionalRenderer.php',
		'ndebug' => '/Debug/Debug.php',
		'ndebugpanel' => '/Debug/DebugPanel.php',
		'ndownloadresponse' => '/Application/Responses/DownloadResponse.php',
		'ndummystorage' => '/Caching/DummyStorage.php',
		'nenvironment' => '/Environment/Environment.php',
		'nextensionreflection' => '/Reflection/ExtensionReflection.php',
		'nfilejournal' => '/Caching/FileJournal.php',
		'nfilestorage' => '/Caching/FileStorage.php',
		'nfileupload' => '/Forms/Controls/FileUpload.php',
		'nforbiddenrequestexception' => '/Application/Exceptions/ForbiddenRequestException.php',
		'nformgroup' => '/Forms/FormGroup.php',
		'nforwardingresponse' => '/Application/Responses/ForwardingResponse.php',
		'nframework' => '/Utils/Framework.php',
		'nfunctionreflection' => '/Reflection/FunctionReflection.php',
		'ngenericrecursiveiterator' => '/Utils/Iterators/GenericRecursiveIterator.php',
		'nhiddenfield' => '/Forms/Controls/HiddenField.php',
		'nhtml' => '/Web/Html.php',
		'nhttpcontext' => '/Web/HttpContext.php',
		'nhttprequest' => '/Web/HttpRequest.php',
		'nhttpresponse' => '/Web/HttpResponse.php',
		'nhttpuploadedfile' => '/Web/HttpUploadedFile.php',
		'nidentity' => '/Security/Identity.php',
		'nimage' => '/Utils/Image.php',
		'nsubmitbutton' => '/Forms/Controls/SubmitButton.php',
		'nimagebutton' => '/Forms/Controls/ImageButton.php',
		'nimagemagick' => '/Utils/ImageMagick.php',
		'ninstancefilteriterator' => '/Utils/Iterators/InstanceFilterIterator.php',
		'ninvalidlinkexception' => '/Application/Exceptions/InvalidLinkException.php',
		'ninvalidpresenterexception' => '/Application/Exceptions/InvalidPresenterException.php',
		'njson' => '/Utils/Json.php',
		'njsonexception' => '/Utils/Json.php',
		'njsonresponse' => '/Application/Responses/JsonResponse.php',
		'nlattefilter' => '/Templates/Filters/LatteFilter.php',
		'nlattemacros' => '/Templates/Filters/LatteMacros.php',
		'nlimitedscope' => '/Loaders/LimitedScope.php',
		'nlink' => '/Application/Link.php',
		'nmailmimepart' => '/Mail/MailMimePart.php',
		'nmail' => '/Mail/Mail.php',
		'nmemcachedstorage' => '/Caching/MemcachedStorage.php',
		'nmethodreflection' => '/Reflection/MethodReflection.php',
		'nmultirouter' => '/Application/Routers/MultiRouter.php',
		'nselectbox' => '/Forms/Controls/SelectBox.php',
		'nmultiselectbox' => '/Forms/Controls/MultiSelectBox.php',
		'nneonparser' => '/Utils/NeonParser.php',
		'nnetteloader' => '/Loaders/NetteLoader.php',
		'nobjectmixin' => '/Utils/ObjectMixin.php',
		'npaginator' => '/Utils/Paginator.php',
		'nparameterreflection' => '/Reflection/ParameterReflection.php',
		'npermission' => '/Security/Permission.php',
		'npresenter' => '/Application/Presenter.php',
		'npresentercomponentreflection' => '/Application/PresenterComponentReflection.php',
		'npresenterloader' => '/Application/PresenterLoader.php',
		'npresenterrequest' => '/Application/PresenterRequest.php',
		'npropertyreflection' => '/Reflection/PropertyReflection.php',
		'nradiolist' => '/Forms/Controls/RadioList.php',
		'nredirectingresponse' => '/Application/Responses/RedirectingResponse.php',
		'nstring' => '/Utils/String.php',
		'nregexpexception' => '/Utils/String.php',
		'nrenderresponse' => '/Application/Responses/RenderResponse.php',
		'nrobotloader' => '/Loaders/RobotLoader.php',
		'nroute' => '/Application/Routers/Route.php',
		'nroutingdebugger' => '/Application/RoutingDebugger.php',
		'nrule' => '/Forms/Rule.php',
		'nrules' => '/Forms/Rules.php',
		'nsafestream' => '/Utils/SafeStream.php',
		'nsendmailmailer' => '/Mail/SendmailMailer.php',
		'nsession' => '/Web/Session.php',
		'nsessionnamespace' => '/Web/SessionNamespace.php',
		'nsimpleauthenticator' => '/Security/SimpleAuthenticator.php',
		'nsimplerouter' => '/Application/Routers/SimpleRouter.php',
		'nsmartcachingiterator' => '/Utils/Iterators/SmartCachingIterator.php',
		'nsnippethelper' => '/Templates/Filters/SnippetHelper.php',
		'nsqlitejournal' => '/Caching/SqliteJournal.php',
		'nsqlitemimic' => '/Caching/SqliteJournal.php',
		'ntemplate' => '/Templates/Template.php',
		'ntemplatecachestorage' => '/Templates/TemplateCacheStorage.php',
		'ntemplatefilters' => '/Templates/Filters/TemplateFilters.php',
		'ntemplatehelpers' => '/Templates/Filters/TemplateHelpers.php',
		'ntextbase' => '/Forms/Controls/TextBase.php',
		'ntextarea' => '/Forms/Controls/TextArea.php',
		'ntextinput' => '/Forms/Controls/TextInput.php',
		'ntools' => '/Utils/Tools.php',
		'nuri' => '/Web/Uri.php',
		'nuriscript' => '/Web/UriScript.php',
		'nuser' => '/Web/User.php',
	);



	/**
	 * Returns singleton instance with lazy instantiation.
	 * @return NNetteLoader
	 */
	public static function getInstance()
	{
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}



	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		$type = ltrim(strtolower($type), '\\');
		if (isset($this->list[$type])) {
			NLimitedScope::load(NETTE_DIR . $this->list[$type]);
			self::$count++;
		}
	}

}

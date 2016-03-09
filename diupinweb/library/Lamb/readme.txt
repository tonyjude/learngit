Lamb Framework v1.0
2012-07-14 21:18
author:小羊
description:
	此框架是从Zend framework得到启发，采用Zend的命名空间格式即：Namespace1_Namespace2_Classname，类文件Classname.php是以
	Namespace1/Namespace2/Classname.php格式的路径存放的。Lamb Framework的命名空间是Lamb为开头，类似与Zend是与Zend开头
	Lamb Framework是一款轻量型的MVC框架，采用PDO兼容多个版本的数据库，自带模版引擎，已实现了基本标签以及自定义标签，模版的
	解析自带有缓存，使用此框架推荐的路径结构
	application
		--controllors
		--views
	library
		--Lamb
	public
		--css
		--html
		--~runtime
	Hello world:
	  -public/index.php
		//设置Lamb framework路径位置
		set_include_path('Lamb framework所在的目录，如：../library/'
			PATH_SEPARATOR . get_include_path);
		//获取类加载器	
		require_once 'Lamb/Loader.php';
		//实例化加载器
		$loader = Lamb_Loader::getInstance();
		//初始化App对象
		$app = Lamb_App::getInstance();
		$app->setControllorPath('Controllor所在的路径，如：../application/controllors/')
			->setViewPath('如果要用到模版引擎则必须要调用此函数和setViewRuntimePath，如：../application/views/')
			->setViewRuntimePath('设置模版文件解析的缓存路径，如：./~runtime/')
			//如果要使用数据库对象则必须调用setSqlHelper设置SQL工具栏和setDbCallback获取Db对象实现数据库操作
			->setSqlHelper('Lamb_Db_Sql_Helper_Abstract 抽象类的子类对象')
			->setDbCallback('Php合法的回调函数或者是实现Lamb_Db_Callback_Interfaced接口的对象')
			->run()//运行;
		-application/controllors/indexControllor.php
		 class indexControllor extends Lamb_Controllor_Abstract //默认的controllor是index
		 {
		 	public function indexAction() //默认的action是index
			{
				echo 'hello world';
				//或者使用View的模版引擎
				include $this->view->load('hello');
			}
		 }
		 -application/views/hello.html
		 <h1>Hello world</h1>
	以下是各个类的介绍：
	-Lamb_Loader
		类加载器，大部分是Zend_Loader_Autoloader和Zend_Loader类的结合
		_defaultInternalAutoload是类的默认加载器，该加载器内部调用getDefaultClassAutoloader获取真正的class加载器
		默认的该类的静态方法loadClass，当然你也可以在得到Loader对象后调用setDefaultClassAutoloader设置默认的加载器
		该方法将实现命名空间同类以下划线_分割的方式，默认只加载以Lamb_开头的类，如果setFallbackAutoloader(true),
		则会尝试加载不带有命名空间的类，如果用户想对自己的命名空间也采用默认的加载器，则可以调用registerNamespaces
		注册命名空间即可，如果用户想对自己的命名空间采用自定义加载器，则可以调用unsiftNamespacesAutoloaders或者
		pushNamespaceAutoloaders方法，想对不带命名空间采用自定义的加载器则调用以上2个函数无需传人第二个参数
		如果一个命名空间注册注册多个加载器，则只要其中一个加载器加载类成功，则不会再调用下面的
	-Lamb_App 
		应用程序类，是整个应用程序的主类，对应用程序的View,Dispatcher,Router,Request,Response,Db,SqlHelper组件
		进行管理和维护，Lamb_App采用单例模式通过调用Lamb_App::getInstance()获取App对象
		App在构造的时候会设置默认的View,Dispatcher,Router,Request,Response对象，并将自身注册到Lamb_Registry类
		中，以便在程序的任何地方都可以通过调用Lamb_App::getGlobalApp()获取全局App对象
		当然用户可以在得到App对象后通过调用SetView,SetDispatcher,SetRouter,SetReuqest,SetResponse等方法设置自己的组件
		如果用户要实现自己的App，则需继承Lamb_App，父类Lamb_App会在其构造函数中自动将其注册到Lamb_Registry类中
		你也可以调用Lamb_App::setGlobalApp()设置Lamb_App::getGlobalApp()返回的类
		在调用Lamb_App::getInstance()得到App对象后，必须要调用Lamb_App::setControllorPath设置Controllor所在的路径
		如果应用程序要使用View则必须要调用Lamb_App::setViewPath设置模版文件的路径，Lamb_App::setViewRuntimePath设置
		模版解析缓存文件保存的路径
		如果程序中要使用到数据库，则必须在得到App对象后，调用Lamb_App::setSqlHelper和Lamb_App::setDbCallback
		设置SQL工具类，sqlHelper是Lamb_Db_Sql_Helper_Abstract子类每个不同版本的数据库工具类的实现不一样，因此将其抽象
		setDbCallback是设置获取Lamb_Db_Abstract对象的回调函数，用于在程序中需要用到Lamb_Db_Abstract对象时通过调用
		Lamb_App::getDb获取数据库对象
		在App使用Router,Dispatcher组件时都难免会出错抛出异常，你可以调用Lamb_App::setErrorHandle设置处理这些错误的类
		默认将直接抛出异常Lamb_App::setErrorHandle设置的是Lamb_App_ErrorHandle_Interfaces实现的子类。
	-Lamb_App_Router
		路由类 可以在程序的任何地方调用Lamb_App::getGlobalApp()->getRouter()得到全局Router
		默认的格式是?s=controllor/action/val1/name1/var2/name2，该类会将解析路由的参数，可调用injectRequest
		将参数注入到Request对象中，可以调用setRouterParamName设置路由参数名，默认是s，调用setUrlDelimiter设置参数分隔符默认是/
		parse方法为该类的解析方法，将路由的参数解析。url方法将参数转换成路由格式的路径
	-Lamb_App_Dispatcher
		分发类 可以在程序的任何地方调用Lamb_App::getGlobalApp()->getDispatcher()得到全局Router
		从Lamb_App_Router对象获取信息，调用对应的controllor，执行该对应的action方法。
		此类要求设置controllor的路径，调用setControllorPath设置，当然也可以调用Lamb_App::setControllorPath
		要求所有的controllor类都要以controllor结尾，如果testControllor则路由参数为test
		所有的action都要以action结尾，如果testAction，默认的controllor是indexControllor,默认的action
		是indexAction，当然用户也可以调用setOrGetDefaultControllor和setOrGetDefaultAction设置默认的controllor和action
		另外还可以设置controllor和action的别，调用setAlias，也就是说假如路由参数为s=index/test
		你可以让indexControllor实际是调用indexAliasControllor,testAction实际调用是testAliasAction
	-Lamb_App_Request
		Http请求类 可以在程序的任何地方调用Lamb_App::getGlobalApp()->getRequest()得到全局Request
		大部分是根据Zend中的Request改写的，Request类实现了__get和__isset方法，因此获取$_userParams,$_GET,$_POST,$_COOKIE,$_SERVER,$_ENV
		的值，可以像类的属性一样读取，获取值的先后顺序就是按照上述的顺序。
		Request类有一个UserParams集合，该集合主要保存的是Router解析后的路由参数和自定义URI时解析的参数，如：s=index/test/v1/n1/v2/n2，
		经过Router解析后将会把v1=>n1,v2=>n2这样的键值对保存到Request的UserParams集合中.
		该类关键的是setRequestUri方法，用户可以自行设置要解析的URI地址，如果设置了，将调用parse_url解析参数，将解析后的参数注入到UserParams集合
		中，如果默认不传参数则将不做任何事情，直接引用PHP原有的GET,POST等集合
	-Lamb_App_Response
		Http响应类，可以在程序的任何地方调用Lamb_App::getGlobalApp()->getResponse()得到全局Response
		该类比较简单，就是把setCookie,setHeader,redirect等方法封装了下
	-Lamb_View
		视图类，可以在程序的任何地方调用Lamb_App::getGlobalApp()->getView()得到全局View
		该类的作用是负责解析模版文件，该类解析2种类型的标签：
		第一种是基本标签，基本标签只实现2种，1，是变量标签，其格式：{$var},{$arrvar[index]}2，是layout标签作用是加载并解析其它
			模版文件，其格式{layout template}。
			扩展：该类保留了PHP标签的作用，同时用户也可以定义自己的基本标签，其步骤是，1.继承Lamb_View类 2，调用父类的setBaseTagParseMap
			方法，注册基本标签解析的正则表达式，注意调用该方法传单的第一个参数$key，其子类一定要实现parse_basetag_$key方法，View匹配到了
			基本标签的字符串，会调用相应的parse_basetag_$key来具体解析此标签
		第二种是自定义标签：
			自定义标签的格式{tag:该标签解析的类全名包括命名空间如：Lamb_View_Tag_List[属性区]}do something{/tag:Lamb_View_Tag_List}
			自定义标签要实现Lamb_View_Tag_Interface接口或者继承Lamb_View_Tag_Abstractc抽象类
			Lamb framework已经默认实现了Lamb_View_Tag_List列表标签 Lamb_View_Tag_Page标签，该2个标签实现了绝大部分分页以及列表标签
			而且可以设置缓存，具体可参见Lamb_View_Tag_List和Lamb_View_Tag_Page文档
	-Lamb_Db_Abstract
		数据库操作抽象类，该类继承PDO，该类对预处理查询已经获取记录的总数进行了优化，在实际的应用中，用户必须根据不同的数据库引擎
		继承并实现该类未实现的抽象方法，在Lamb framework中，只认Lamb_Db_Abstract类型中所有实现的方法。
		你可以在得到App对象时，调用setDbCallback设置获取该子类的对象回调方法，这样程序就可以在任何地方调用
		Lamb_App::getGlobalApp()->getDb()获取数据操作对象，如果为设置Dbcallback，或者调用回调方法，getDb方法将抛出异常
		在原始的PDO调用PDO::query方法将返回一个PDOStatement对象，但Lamb framework规定，在得到db对象以后，一律调用
		PDO::setAttribute(PDO::ATTR_STATEMENT_CLASS,array('Lamb_Db_RecordSet', array($objInstance)))方法，设置PDO::query
		返回的对象是Lamb_Db_RecordSet类或者其子类的对象。
		目前Lamb framework只实现子类Lamb_Mssql_Db
	-Lamb_Db_RecordSet
		记录集对象，该对象不能直接实例化，只能是Lamb_Db_Abstract::query或者prepare方法返回该对象
		该类继承了PDOStatement类，并实现了Lamb_Db_RecordSet_Interface接口中的方法，该类优化了
		rowCount方法，尤其是对于查询的记录集比较大的时候，通过调用getRowCount方法获取记录集的总数
		此类有个不便之处，就是对于有union关键字的SQL语句无法100%智能判断，因此在有时候用户需要调用
		setHasUnion方法设置该SQL语句是否含有Union关键字
		如果用户需要实现自己的RecordSet，可以实现Lamb_Db_RecordSet_CustomInterface接口来定义数据源或者直接
		继承Lamb_Db_RecordSet类
	-Lamb_Db_Sql_Helper_Abstract
		SQL工具抽象类，对SQL中的Select语句操作的工具类，由于不同的数据库引擎可能含有不同的数据库语法，因此此类为
		抽象类，用户在实际的应用中，必须继承此类，实现其未实现的方法，并在得到App对象后，调用Lamb_App::setSqlHelper
		设置自定义SQL工具类，程序可以在任何地方调用Lamb_App::getGlobalApp()->getSqlHelper()得到sqlHelper对象
		Lamb framework默认只实现了Lamb_Mssql_Sql_Helper子类
	-Lamb_Db_Table
		数据库表类，封装了基本的数据库的查询，修改，插入语句，建议使用此类进行SQL的查询，修改，插入
	-Lamb_Db_Select
		数据库查询类，该类封装了数据库的插操作，包括普通查询，带缓存查询，分页查询，预处理查询，预处理分页查询
	-Lamb_Cache_File
		文件缓存
	-Lamb_Cache_Memcached
		Memcached缓存类
	-Lamb_Registry
		注册全局类，通过调用Lamb_Registry::set()方法，然后在程序的任何地方都可以调用Lamb_Registry::get()得到
		比较简单的一个类
	-Lamb_Upload
		上传类，可以实现一个或多个文件上传，可以限制上传文件的扩展名，大小等
	-Lamb_Utils
		工具类，对常用的一些变量的判断，数据的加密等
	-Lamb_Debuger
		调试类
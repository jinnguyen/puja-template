Puja v1.1
====

Puja is a lightweight, flexible and easy PHP  template engine. Inspired in django, Puja also support validate template syntax!

<strong>Install:</strong><br />
<pre>
composer require jinnguyen/puja-template
require '/path/to/vendor/autoload.php';

class CustomFilter extends \Puja\Template\Lexer\Filter\FilterAbstract
{
    public function dateFilter($var, $args) {
        return abs($var);
    }
}

class CustomTag extends \Puja\Template\Lexer\Tag\TagAbstract
{
    public function cssTag($arg)
    {
        return '<style src="' . $arg . '" />';
    }

    public function javascriptTag($arg)
    {
        return '<style src="' . $arg . '" />';
    }
}

$puja = new \Puja\Template\Template(array(
    /**
    * Folders that contain template files and the last folder is higher priority 
    * Ex: templateDirs = ['/path/to/template/Default', '/path/to/template/2017'] and file test.tpl is in both /path/to/template/Default and /path/to/template/2017.
    * Then /path/to/template/2017/test.tpl (the last folder) will be used
    */
    'templateDirs' => [
      __DIR__ . '/templates/Default',
      __DIR__ . '/templates/2017'
    ],
    /**
    * Cached folder contains generated-files by Puja-Template
    */
    'cacheDir' => __DIR__ . '/cache/',
    /** Cache level, current we just support 3 levels
    * 0: no cache, (Puja-Template will re-generate every time)
    * 1: smart cache, (Puja-Template generate in the first time and ONLY re-genertate when file template (.tpl) has changed
    * 2: hard cache ( Puja-Template never re-generate files until genereted file has been deleted.)
    */
    'cacheLevel' => 0,
    /**
    * customTag class, default: NULL
    */
    'customTag' => 'CustomTag',
    /**
    * customFilter class, default: NULL
    */
    'customFilter' => 'CustomFilter',
    /**
    * On/off mode debug
    */
    'debug' => true,
));
</pre>

<strong>Some of Puja-Template's features</strong>:
* <strong>VALIDATE TEMPLATE SYNTAX</strong>
* it is extremely fast
* no template parsing overhead, only compiles once.
* it is smart about recompiling only the template files that have changed.
* unlimited nesting of sections, conditionals, etc.
* built-in caching of template output.
* Smart access variable value, ex: {{ a.b }} will access like $a->b if $a is a obj, and $a['b'] if $a is array.

<strong>Validate syntax:</strong><br />
Puja support validate syntax before the parser run compiler. This will helpfull for you to write template syntax.

Bug list:
https://github.com/jinnguyen/puja/issues?page=1&state=open


Example:
file template: index.tpl:
<pre>{% extends master.tpl %}
{% block body %}
	Hello, {{ a }
	Welcome you go to Puja template examples
{% endblock %}</pre>

The result will be:
<pre>
<img src="https://raw.github.com/jinnguyen/puja-template/master/docs/images/Template-syntax-error.png" /></pre>

Puja only run debug when mode <strong>debug</strong> is enabled<br />
**  We recommend you should only enable mode <strong>debug</strong>  when your app is in develop. And disable it when your app go to production. It will save a lot time to template engine parser.
<br /><br />
<strong>Basic API Usage</strong>:<br />
- template file: index.tpl
<pre>Hello <strong>{{ username }}</strong>,
Welcome you go to the very first exmplate of Puja template.</pre>

- php file: index.php
```php

  $data = array(
  	'username'=>'Jin Nguyen',
  );
  $tpl->parse($template_file = 'index.tpl', $data);
```

The result will show:
<pre>
Hello <strong>Jin Nguyen</strong>,
Welcome you go to the very first exmplate of Puja template.</pre>

See <a href="https://github.com/jinnguyen/puja/tree/master/docs/user-guide.md">User's guide</a> for full information.<br />

<strong>Template Inheritance</strong>:<br />
- master.tpl:
<pre>==== Start Master ===
{% block body %}Master Body{% endblock body %}
{% block javascript %}Master javascript{% endblock javascript %}
==== End Master ====</pre>

- index.tpl
<pre>
{% block javascript %}<strong>Index javascript [{{ block.supper }}]</strong>{% endblock %}
{% block body %}<strong>Index Body</strong>{% endblock %}</pre>

And the result will be:

<pre>==== Start Master ===
<strong>Index Body [Master Body]</strong>
<strong>Index javascript</strong>
==== End Master ====</pre>
<a href="https://github.com/jinnguyen/puja/tree/master/docs">more detail &gt;&gt; </a>






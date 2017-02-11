Custom template tags
===================
Puja’s template system comes with a wide variety of <a href="https://github.com/jinnguyen/puja/blob/master/docs/built-in-tags.md">built-in tags</a> and <a href="https://github.com/jinnguyen/puja/blob/master/docs/built-in-filters.md">filters</a> designed to address the presentation logic needs of your application. 
Hoever, sometime you may find yourself needing functionality that is not covered by the core set of template primitives. You can extend the template engine by defining custom tags and filters using PHP, and then make them available to your template.

- <strong>I. Writing custom template filters</strong><br />
Custom filters are just method of PHP class.<br />
For example, you need a new filter <strong>cutend</strong> to cut a number end characters of the string.<br />
Here’s an example filter definition in PHP:<br />
<pre>
&lt;php

class CustomFilter extends \Puja\Template\Lexer\Filter\FilterAbstract
{
    public function cutendFilter($var, $arg = null) {
        if (empty($arg)) {
            return $var;
        }
        return substr($var, -1 * $arg);
    }
}

$puja = new \Puja\Template\Template(array(
    //.....
    'customFilter' => 'CustomFilter',
));
</pre>
And here’s an example of how that filter would be used:
<pre>{{ name|cutend:4 }} // have argument
  {{ name|cutend }} // no argument
</pre>
If <strong>name</strong> is "I like puja", the output will be "puja" and "I like puja".<br />
** You can overwrite all built-in filter (even filter <a href="https://github.com/jinnguyen/puja/blob/master/docs/built-in-filters.md#main">main</a>) by set the customer filter name same with built-in filter name.

- <strong>II. Writing custom template tags</strong><br />
Same with customer template filters, customer tags are just method of PHP class.
For example, you need a new tag <strong>css</strong> to control version of css file.
Here’s an example tags definition in PHP:<br />
<pre>
&lt;php
class CustomTag extends \Puja\Template\Lexer\Tag\TagAbstract
{
    function css($var, $arg = null){
        return '<css src="' . $var . '?v=1" />';
    }
}

$puja = new \Puja\Template\Template(array(
    //....
    'customTag' => 'CustomTag',
));
</pre>
And here’s an example of how that filter would be used:
<pre>{% css style.css %}</pre>
The output will be &lt; src="style.css?v=1" /&gt;

** <i>Filter and tag methods should always return something. They shouldn’t raise exceptions. They should fail silently. In case of error, they should return either the original input or an empty string – whichever makes more sense.</i>

{%    extends       master.tpl %}

{% block body %}
{{ block.supper }} 
{# dafad f#} he hla {# a #} lal kd "kafjkdjlk" alkf 
sss ss
==========TEST FILE '{$ skin $}'=========
{% set a="dddd" c='dddd' %}
{% include include1.tpl kk="ddd" rs='kak  kak kak ak rs = 5' o=uc  t=0 e=''   news=data.lists %}
{% include left.tpl %}
{% get_file include1.tpl %}
Block BODY
{{ a }} 
Skin: {$ skin $} =  {$ abc $} == {$ skin $}
{% if c in d %}fadfas{% else %}ooo{% endif %}
==========
{% endblock %}
{% block css %} {{ block.supper }} Test CSS {% endblock %}
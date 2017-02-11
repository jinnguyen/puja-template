*** Left overwrite **
{% if pro in products.product and b %}
it is a product
{% endif %}

{% for rs in data.list %}
    {% if a or b %}
    a = b
    {% elseif a == c %}
    a = c
    {% elseif a == d %}
    a = d
    {% else %}
        a is notthing;
    {% endif %}
{% empty %}
    echo adfaf
{% endfor %}

{% for key,rs in data.news %}

{% endfor %}
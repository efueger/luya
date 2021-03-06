Show Navigation in the Frontend Layout
======================================

27.2.2015: the $lang variable changed to $composition. The $composition->langShortCode variable will be $composition->langShortCode. compoition full provideds the full prefix compostion url path.

To show the navigation tree you need to access the link component provided by ```Yii::$app```. You have multiple possibilities by changing the arguments of
```Yii::$app->links->findByArguments()```. Assuming a default category, a language variable set in ```$composition->langShortCode``` and the id of the parent cms page is 0 (= top level),
you'll get the first hierarchy as an array by calling ```Yii::$app->links->findByArguments(['cat' => 'default', 'lang' => $composition->langShortCode, 'parent_nav_id' => 0])```

Full Navigation Tree
--------------------

By nesting multiple foreach loops and using the the last item id as the current parent id you can parse the entire navigation tree.

Attention: in production you should check if ```Yii::$app->links->findByArguments(['lang' => $composition->langShortCode, 'parent_nav_id' => $item['id']])``` is returning an array and not null before looping.

Example - showing three levels of the navigation tree:
```
<ul>
<? foreach(Yii::$app->links->findByArguments(['cat' => 'default', 'lang' => $composition->langShortCode, 'parent_nav_id' => 0]) as $item): ?>
        <li><a href="<?= $composition->getFull() . $item['url'];?>"><?= $item['title']; ?></a>
            <ul>
                <? foreach(Yii::$app->links->findByArguments(['lang' => $composition->langShortCode, 'parent_nav_id' => $item['id']]) as $subItem): ?>
                <li><a href="<?= $composition->getFull() . $subItem['url'];?>"><?= $subItem['title']?></a>
                <ul>
                    <? foreach(Yii::$app->links->findByArguments(['lang' => $composition->langShortCode, 'parent_nav_id' => $subItem['id']]) as $subSubItem): ?>
                    <li><a href="<?= $composition->getFull() . $subSubItem['url'];?>"><?= $subSubItem['title']?></a>
                    <? endforeach; ?>
                </ul>
                <? endforeach; ?>
            </ul>
        </li>
    <? endforeach; ?>
</ul>
```

Snapshot of the Navigation Tree
-------------------------------

If you want to show only the part of the navigation tree which is currently viewed by the web user, you'll have to determine the active part of each level in the hierarchy by
accessing the helper function ```\luya\helpers\Menu::parentNavIdByCurrentLink(\yii::$app->links, 1)``` (for level = 1).

Example - showing three levels of the navigation hierarchy:

```
<!-- FIRST LEVEL -->
<ul>
    <? foreach(Yii::$app->links->findByArguments(['cat' => 'default', 'lang' => $composition->langShortCode, 'parent_nav_id' => \luya\helpers\Menu::parentNavIdByCurrentLink(\yii::$app->links, 1)]) as $item): ?>
        <li><a href="<?= $composition->getFull() . $item['url'];?>"><?= $item['title']; ?></a></li>
    <? endforeach; ?>
</ul>

<!-- SECOND LEVEL -->
<ul>
    <? foreach(Yii::$app->links->findByArguments(['cat' => 'default', 'lang' => $composition->langShortCode, 'parent_nav_id' => \luya\helpers\Menu::parentNavIdByCurrentLink(\yii::$app->links, 2)]) as $item): ?>
        <li><a href="<?= $composition->getFull() . $item['url'];?>"><?= $item['title']; ?></a></li>
    <? endforeach; ?>
</ul>

<!-- THIRD LEVEL -->
 <ul>
    <? foreach(Yii::$app->links->findByArguments(['cat' => 'default', 'lang' => $composition->langShortCode, 'parent_nav_id' => \luya\helpers\Menu::parentNavIdByCurrentLink(\yii::$app->links, 3)]) as $item): ?>
        <li><a href="<?= $composition->getFull() . $item['url'];?>"><?= $item['title']; ?></a></li>
    <? endforeach; ?>
</ul>
```
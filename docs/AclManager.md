# Access Control Level Manager

It provides access management based on URL, such as you can visit `user/logout` but not `user/create`

## Menus

Menus are defined as a collection of URLs, it list all the APIs that need controlling as following:

```php
<?php

return [
    ['label' => '用户管理', 'children' => [
        ['label' => '用户列表', 'url' => 'user/index'],
        ['label' => '用户权限', 'url' => 'user/privilege', 'children' => [
            ['label' => '添加角色', 'url' => 'user/role'],
            ['label' => '修改角色', 'url' => 'user/role'],
        ]],
    ]],
];

```

and the definition above should to be render by frontend engineer as a tree

```text
- 用户管理
    - 用户列表
    - 用户权限
        - 添加角色
        - 修改角色
```

### Basic Format

```
['label' => 'text', 'url' => '', 'children' => []]
```

### Properties

- label

    Text to show

- uri

    Access URI, usually URL of API or route of web

- children

    Children menus, which could have full structure as menus. This is the most complicated field overall. There can be arbitrary level of children.


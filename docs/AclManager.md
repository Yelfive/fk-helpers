# Access Control Level Manager

It provides access management based on URL, such as you can visit `user/logout` but not `user/create`

## Menus

Menus are defined as a collection of URLs(for front end) and APIs(for api access management)

**see** `/src/privilege/menu.php` for example

and the definition should to be render by frontend engineer as a tree

```text
- 用户管理
    - 用户列表
    - 用户权限
        - 添加角色
        - 修改角色
```

### Basic Format

```
['label' => 'text', 'url' => '', 'apis' => ['api' => METHOD],'children' => []]
```

### Properties

- `label` _string_

    Text to show

- `url` _string_

    URL for frontend, to display ,to access page

- `apis` _array_

    An array defines APIs accessible bound to `url`, it contains two parts, `api name` and corresponding `method`
    in form of

    ```text
    ['api' => METHOD(s)]
    ```
    
    - `api` _string_
    
        The api name
    
    - `methods` _integer_

        Methods as integer to indicates HTTP method allowed for `api`
        
        This should be const from `fk\helpers\AclManager::METHOD_*`, and can be use `nor` for multiple methods applied on one `api`

- `children` _array_

    Children menus, which could have full structure as its parent. This is the most complicated field overall. There can be arbitrary level of children.


## How to implement

### 1. Configure the menu.php of yours

Refer to [menu.php](./src/privilege/menu.php) for example

### 2. Saving in database


Saving in database the `urls` allowed for front end


```php
<?php

/** @var array $menus */
(new \fk\helpers\privilege\AclManager($menus))->
```
### 3. 
<?php

return [
    /**
     * Where to store factories. If no path is given the classes will
     * be stored internally within the vendor directory.
     */
    'path' => null,

    /**
     * Include hash in class name to ensure there are no conflict if the
     * config changes.
     */
    'hash' => false,

    /**
     * The name used in factory classes. E.g. UserFactory.
     */
    'suffix' => 'Factory',

    /**
     * Namespace of the generated classes
     */
    'namespace' => '',

    /**
     * Model namespaces of your models. The first match is used. If that
     * doesn't work for you the solution is to set the 'path' to somewhere in
     * your project.
     */
    'model_namespaces' => [
        'App',
    ]
];


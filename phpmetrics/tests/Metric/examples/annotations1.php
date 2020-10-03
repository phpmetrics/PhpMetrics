<?php
namespace A {

    class Route
    {
    }
}

namespace B {

    class Json
    {

    }

    class Nothing {

    }
}
namespace C {

    use A\Route;
    use B\Json;

    class A
    {
        /**
         * @Route
         * @Nohing
         * @Json("xyz")
         */
        public function fooAction()
        {

        }

    }
}
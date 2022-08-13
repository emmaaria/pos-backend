<?php //d995ca709671cc7efa5bdf19f931bbc3
/** @noinspection all */

namespace Illuminate\Foundation\Console {

    /**
     * @method static void dispatch(array $data)
     * @method static void dispatchNow(array $data)
     */
    class QueuedCommand {}
}

namespace Illuminate\Queue {

    use Laravel\SerializableClosure\SerializableClosure;

    /**
     * @method static void dispatch(SerializableClosure $closure)
     * @method static void dispatchNow(SerializableClosure $closure)
     */
    class CallQueuedClosure {}
}

<?php

namespace CultureGr\Presenter;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;

/**
 * Extension methods for collections.
 *
 * This class provides macros that extend Laravel's Collection class to support
 * presenting collections and paginators using the Presentable trait. The goal is to
 * provide a consistent, fluent interface for presenting models, collections, and
 * paginators, allowing for code like $users->presentCollection(UserPresenter::class)
 * to work seamlessly regardless of whether $users is a regular collection or a paginator.
 */
class CollectionMacros
{
    /**
     * Register the collection macros.
     *
     * This method registers the presentCollection macro on the Collection class.
     * The macro is designed to intelligently detect whether it's being called directly
     * on a Collection or indirectly through a paginator's __call method, and adjust
     * its behavior accordingly. This provides a unified API where both collections and
     * paginators can use the same method with identical syntax, but get appropriately
     * formatted results.
     */
    public static function register()
    {
        /**
         * The presentCollection macro allows presenting a collection of models using a presenter class.
         *
         * This macro works with both regular collections and paginators, providing a consistent API.
         *
         * How Paginator Integration Works:
         *
         * This macro is not directly registered on the Illuminate\Contracts\Pagination\LengthAwarePaginator or
         * Illuminate\Contracts\Pagination\Paginator classes, as these classes are not directly macroable.
         *
         * Instead, it relies on Laravel's AbstractPaginator implementation of the __call magic method,
         * which proxies unknown method calls to the paginator's Collection instance.
         * Since Collection is macroable, this effectively makes the paginator inherit this functionality.
         *
         * When $paginator->presentCollection() is called:
         * 1. AbstractPaginator's __call catches the call to the non-existent method
         * 2. __call proxies the call to the paginator's items collection
         * 3. Our macro executes on the collection
         * 4. Our backtrace detection identifies that we're being called from a paginator
         * 5. We call Presenter::pagination() instead of Presenter::collection()
         *
         * Paginator Detection Logic:
         *
         * We use debug_backtrace to determine if this macro is being called through a paginator's __call method.
         * This is necessary because when a method is called on a paginator that doesn't exist (like presentCollection),
         * Laravel's AbstractPaginator proxies the call to the underlying Collection via __call.
         *
         * We need to detect this scenario so we can return the appropriate format:
         * - For regular collections: just the presented items
         * - For paginators: the full pagination structure with data, links, and meta
         *
         * This approach ensures that $collection->presentCollection() and $paginator->presentCollection()
         * both work as expected, with the latter producing the same result as Presenter::pagination($paginator).
         *
         * This provides a consistent API while respecting the different output formats needed for
         * collections vs. paginators.
         *
         * See: https://www.stephenlewis.me/blog/laravels-mysteriously-macroable-paginators/
         */
        Collection::macro('presentCollection', function (string $presenterClass) {
            // Validate that the presenter class is a subclass of Presenter
            if (!is_subclass_of($presenterClass, Presenter::class)) {
                throw new \InvalidArgumentException(
                    "The presenter class must be a subclass of " . Presenter::class
                );
            }

            // Use debug_backtrace to detect if we're being called from a paginator's __call method
            $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
            $isPaginator = false;
            $paginator = null;

            if (count($trace) >= 3) {
                $callerMethod = $trace[1]['function'] ?? '';
                $callerClass = $trace[1]['class'] ?? '';

                // Check if we're being called from __call method of a paginator
                if ($callerMethod === '__call' && isset($trace[2]['object'])) {
                    $paginator = $trace[2]['object'];
                    if ($paginator instanceof Paginator || $paginator instanceof LengthAwarePaginator) {
                        $isPaginator = true;
                    }
                }
            }

            // If this is a paginator, use the pagination method to get the full pagination structure
            if ($isPaginator && $paginator !== null) {
                return $presenterClass::pagination($paginator);
            }

            // Otherwise, use the collection method for regular collections
            return $presenterClass::collection($this);
        });
    }
}

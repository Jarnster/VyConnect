<?php
require_once 'includes/utils.php';

if (!class_exists('Caching')) {
    class Caching
    {
        private string $cacheDir;
        private int $cacheLifetime;

        // Constructor: Initializes the cache directory and lifetime, creates the directory if it doesn't exist
        public function __construct(string $cacheDir, int $cacheLifetime = 60)
        {
            $this->cacheDir = $cacheDir;
            $this->cacheLifetime = $cacheLifetime;

            // Create cache directory if it doesn't exist
            if (!is_dir($this->cacheDir)) {
                mkdir($this->cacheDir, 0777, true);
            }
        }

        // Function to get the cached response for a given cache key, returns null if cache is expired or doesn't exist
        public function getCachedResponse(string $cacheKey)
        {
            // Get the cache file path based on the selected router index
            $cacheFile = $this->getCacheFilePath($cacheKey);

            // Check if the cache file exists
            if (file_exists($cacheFile)) {
                $cacheData = json_decode(file_get_contents($cacheFile), true);

                // Check if the cache is still valid based on the lifetime
                if (time() - $cacheData['timestamp'] < $this->cacheLifetime) {
                    return $cacheData['response'];
                }
            }

            return null;
        }

        // Function to cache the response for a given cache key
        public function cacheResponse(string $cacheKey, $response): void
        {
            // Get the cache file path
            $cacheFile = $this->getCacheFilePath($cacheKey);

            // Prepare the cache data with timestamp and response
            $cacheData = [
                'timestamp' => time(),
                'response' => $response
            ];

            // Create the cache file and write the data
            file_put_contents($cacheFile, json_encode($cacheData));
        }

        // Helper function to generate the cache file path for a given cache key
        private function getCacheFilePath(string $cacheKey): string
        {
            // Get the current router index for multi-router support
            $routerIndex = get_selected_router_index();

            // Ensure the subdirectory for the router index exists
            $routerDir = "{$this->cacheDir}/{$routerIndex}";
            if (!is_dir($routerDir)) {
                mkdir($routerDir, 0777, true); // Create the directory if it doesn't exist
            }

            // Create the cache file path using the router index and cache key
            return "{$routerDir}/{$cacheKey}.json";
        }

        // Helper function to mark all cache files of a specific router as invalid
        public function invalidateRouterCache(int $routerIndex): void
        {
            // Get the path to the router's cache directory
            $routerDir = "{$this->cacheDir}/{$routerIndex}";

            // Check if the directory exists
            if (is_dir($routerDir)) {
                // Scan the directory to get all cache files for the router
                $cacheFiles = glob("{$routerDir}/*.json");

                // Loop through each cache file and invalidate it
                foreach ($cacheFiles as $cacheFile) {
                    $cacheData = json_decode(file_get_contents($cacheFile), true);

                    // Mark the cache as invalid by setting the timestamp to 0
                    $cacheData['timestamp'] = 0;

                    // Write the invalidated cache data back to the file
                    file_put_contents($cacheFile, json_encode($cacheData));
                }
            }
        }
    }
}

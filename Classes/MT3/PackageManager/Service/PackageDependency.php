<?php
namespace MT3\PackageManager\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "MT3.PackageManager".    *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class PackageDependency {
        
    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;
    
    /**
     * @param string $packageKey
     * @return bool
     */
    public function getPackageRequirements($packageKey) {
        $packageName = $this->packageManager->getAvailablePackages()[$packageKey]->getComposerManifest()->name;
        
        foreach($this->packageManager->getActivePackages() as $key => $package) {
            
            if (property_exists($package->getComposerManifest(), 'require') == true &&
                array_key_exists($packageName, $package->getComposerManifest()->require)) {
                    
                return $key;
            }
        }
        return false;
    }

    /**
     * @param string $packageKey
     * @return bool
     */
    public function getMissingPackageRequirements($packageKey) {
        if (property_exists($this->packageManager->getAvailablePackages()[$packageKey]->getComposerManifest(), 'require') == true) {
            $requiredPackages = $this->packageManager->getAvailablePackages()[$packageKey]->getComposerManifest()->require;
            foreach(array_keys((array) $requiredPackages) as $requiredPackageName) {
                // Simple check if requirement is a package
                if(strpos($requiredPackageName, '/') === false) {
                     continue;
                }
                
                if ($this->isPackageActiveByName($requiredPackageName) === false) {
                    return $requiredPackageName;
                }
            }
            return false;
        }
    }
    
    /**
     * @param string $packageName
     * @return bool
     */
    protected function isPackageActiveByName($packageName) {
        $foundPackage = false;
        
        foreach($this->packageManager->getActivePackages() as $key => $package) {
            if (property_exists($package->getComposerManifest(), 'name') == true &&
                $package->getComposerManifest()->name == $packageName) {
                
                $foundPackage = true;
                break;
            }
        }
        return $foundPackage;
    }
}
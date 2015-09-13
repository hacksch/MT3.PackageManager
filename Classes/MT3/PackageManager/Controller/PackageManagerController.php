<?php
namespace MT3\PackageManager\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "MT3.PackageManager".    *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class PackageManagerController extends \TYPO3\Flow\Mvc\Controller\ActionController {

    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;
    
    /**
     * Activated packages
     */
    protected $activePackages;
    
    /**
     * Available packages
     */
    protected $availablePackages;

    /**
     * Initialize action
     */
    public function initializeAction() {
        $this->availablePackages = $this->packageManager->getAvailablePackages();
        $this->activePackages = $this->packageManager->getActivePackages();
    }

	/**
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('availablePackages', $this->availablePackages);
        $this->view->assign('activePackages', $this->activePackages);
	}

    /**
     * @param string $packageKey
     * @return void
     */
    public function activateAction($packageKey) {
        if ($this->packageManager->isPackageAvailable($packageKey) === false) {
            $this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error('Package '. $packageKey .' does not exists.'));
            $this->redirect('index');
        }
        
        if ($this->packageManager->isPackageActive($packageKey) === true) {
            $this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error('Package '. $packageKey .' is already activated.'));
            $this->redirect('index');
        }
        
        if ($this->hasMissingRequirements($packageKey) === false) {
            $this->packageManager->activatePackage($packageKey);
        }
        
        $this->redirect('index');
    }

    /**
     * @param string $packageKey
     * @return void
     */
    public function deleteAction($packageKey) {
        if ($this->packageManager->isPackageAvailable($packageKey) === false) {
            $this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error('Package '. $packageKey .' does not exists.'));
            $this->redirect('index');
        }
        
        if ($this->packageManager->isPackageActive($packageKey) === true) {
            $this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error('Package '. $packageKey .' is currently activated.'));
            $this->redirect('index');
        }
        
        $this->packageManager->deletePackage($packageKey);
        $this->redirect('index');
    }

    /**
     * @param string $packageKey
     * @return void
     */
    public function deactivateAction($packageKey) {
                
        if ($this->packageManager->isPackageAvailable($packageKey) === false) {
            $this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error('Package '. $packageKey .' does not exists.'));
            $this->redirect('index');
        }
        
        if ($this->packageManager->isPackageActive($packageKey) === false) {
            $this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error('Package '. $packageKey .' is currently not activated.'));
            $this->redirect('index');
        }
        
        if ($this->isPackageRequired($packageKey) === false) {
            $this->packageManager->deactivatePackage($packageKey);
        }
        
        $this->redirect('index');
    }
    
    /**
     * @param string $packageName
     * @return bool
     */
    private function isPackageActiveByName($packageName) {
        $foundPackage = false;
        
        foreach($this->activePackages as $key => $package) {
            if (property_exists($package->getComposerManifest(), 'name') == true &&
                $package->getComposerManifest()->name == $packageName) {
                
                $foundPackage = true;
                break;
            }
        }
        
        return $foundPackage;
    }
    
    /**
     * @param string $packageKey
     * @return bool
     */
    private function isPackageRequired($packageKey) {
        $packageName = $this->availablePackages[$packageKey]->getComposerManifest()->name;
        
        foreach($this->activePackages as $key => $package) {
            
            if (property_exists($package->getComposerManifest(), 'require') == true &&
                array_key_exists($packageName, $package->getComposerManifest()->require)) {
                    
                $this->flashMessageContainer->addMessage(
                    new \TYPO3\Flow\Error\Error('Package '. $packageKey .' is required from '. $key .'.')
                );
                $this->redirect('index');
            }
        }
        
        return false;
    }

    /**
     * @param string $packageKey
     * @return bool
     */
    private function hasMissingRequirements($packageKey) {
        if (property_exists($this->availablePackages[$packageKey]->getComposerManifest(), 'require') == true) {
            $requiredPackages = $this->availablePackages[$packageKey]->getComposerManifest()->require;
            foreach(array_keys((array) $requiredPackages) as $requiredPackageName) {
                // Simple check if requirement is a package
                if(strpos($requiredPackageName, '/') === false) {
                     continue;
                }
                
                if ($this->isPackageActiveByName($requiredPackageName) === false) {
                    $this->flashMessageContainer->addMessage(
                        new \TYPO3\Flow\Error\Error('Package '. $packageKey .' requires '. $requiredPackageName .'.')
                    );
                    $this->redirect('index');
                }
            }
            return false;
        }
    }
}
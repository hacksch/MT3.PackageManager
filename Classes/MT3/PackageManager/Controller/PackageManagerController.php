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
     * @var \MT3\PackageManager\Service\PackageDependency
     * @Flow\Inject
     */
    protected $packageDependencyService;
    
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
        
        if ($requiredPackage = $this->packageDependencyService->getMissingPackageRequirements($packageKey)) {
            $this->flashMessageContainer->addMessage(
                new \TYPO3\Flow\Error\Error('Package '. $packageKey .' could not activate. '. $requiredPackage .' not active or missing.')
            );
            $this->redirect('index');
        }
        
        $this->packageManager->activatePackage($packageKey);
        
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
        
        if ($requiredBy = $this->packageDependencyService->getPackageRequirements($packageKey)) {
            $this->flashMessageContainer->addMessage(
                new \TYPO3\Flow\Error\Error('Package '. $packageKey .' is required by '. $requiredBy)
            );
            $this->redirect('index');
        }
        
        $this->packageManager->deactivatePackage($packageKey);
        
        $this->redirect('index');
    }
}
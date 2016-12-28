<?php
// src/EMS/CoreBundle/Entity/User.php

namespace EMS\CoreBundle\Entity;


interface User
{
    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated();
    
    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified();

    /**
     * Get circles
     *
     * @return array
     */
    public function getCircles();
    
    
    /**
     * Set circles
     *
     * @param \ObjectPickerType $circles
     *
     * @return User
     */
    public function setCircles($circles);

    /**
     * Set displayName
     *
     * @param string $displayName
     *
     * @return User
     */
    public function setDisplayName($displayName);

    /**
     * Get displayName
     *
     * @return string
     */
    public function getDisplayName();

    /**
     * Set allowedToConfigureWysiwyg
     *
     * @param boolean $allowedToConfigureWysiwyg
     *
     * @return User
     */
    public function setAllowedToConfigureWysiwyg($allowedToConfigureWysiwyg);

    /**
     * Get allowedToConfigureWysiwyg
     *
     * @return boolean
     */
    public function getAllowedToConfigureWysiwyg();

    /**
     * Set wysiwygProfile
     *
     * @param string $wysiwygProfile
     *
     * @return User
     */
    public function setWysiwygProfile($wysiwygProfile);

    /**
     * Get wysiwygProfile
     *
     * @return string
     */
    public function getWysiwygProfile();

    /**
     * Set wysiwygOptions
     *
     * @param string $wysiwygOptions
     *
     * @return User
     */
    public function setWysiwygOptions($wysiwygOptions);

    /**
     * Get wysiwygOptions
     *
     * @return string
     */
    public function getWysiwygOptions();

    /**
     * Set layoutBoxed
     *
     * @param boolean $layoutBoxed
     *
     * @return User
     */
    public function setLayoutBoxed($layoutBoxed);

    /**
     * Get layoutBoxed
     *
     * @return boolean
     */
    public function getLayoutBoxed();

    /**
     * Set sidebarMini
     *
     * @param boolean $sidebarMini
     *
     * @return User
     */
    public function setSidebarMini($sidebarMini);

    /**
     * Get sidebarMini
     *
     * @return boolean
     */
    public function getSidebarMini();

    /**
     * Set sidebarCollapse
     *
     * @param boolean $sidebarCollapse
     *
     * @return User
     */
    public function setSidebarCollapse($sidebarCollapse);

    /**
     * Get sidebarCollapse
     *
     * @return boolean
     */
    public function getSidebarCollapse();

    /**
     * Add authToken
     *
     * @param \EMS\CoreBundle\Entity\AuthToken $authToken
     *
     * @return User
     */
    public function addAuthToken(\EMS\CoreBundle\Entity\AuthToken $authToken);

    /**
     * Remove authToken
     *
     * @param \EMS\CoreBundle\Entity\AuthToken $authToken
     */
    public function removeAuthToken(\EMS\CoreBundle\Entity\AuthToken $authToken);

    /**
     * Get authTokens
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthTokens();
}
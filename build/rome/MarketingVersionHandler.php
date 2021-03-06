<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Class that handles calculation of, and formatting of, the Sugar Marketing
 * Version string based on the typical version string presented to the builder.
 */
final class MarketingVersionHandler
{
    /**
     * When we cannot figure out the marketing version, just use an empty string.
     * @var string
     */
    private $default = '';

    /**
     * Format for the marketing version. This is not, and should not be,
     * configurable.
     * @var string
     */
    private $format = "%s 20%d";

    /**
     * Patterns to use for calculating the marketing version, going back to 7.10.x.
     * @var array
     */
    private $versionPatterns = [
        '^7\.10\.[\d]+\.[\d]+$' => [
            'method' => 'getFormattedVersion',
            'args' => ['season' => 'Fall', 'year' => 17],
        ],
        '^7\.11\.[\d]+\.[\d]+$' => [
            'method' => 'getFormattedVersion',
            'args' => ['season' => 'Winter', 'year' => 18],
        ],
        '^[\d]{1,2}\.[\d]+\.[\d]+' => [
            'method' => 'getCalculatedVersion',
        ],
    ];

    /**
     * Quarters for the marketing versions
     * @var array
     */
    private $quarters = [
        'Q2',
        'Q3',
        'Q4',
        'Q1',
    ];

    /**
     * Gets the formatted marketing version.
     * @param array $args Takes in an array of season and year
     * @return string
     */
    private function getFormattedVersion(array $args) : string
    {
        // If we don't have what we need then just bail
        if (!isset($args['quarter'], $args['year'])) {
            return $this->default;
        }

        // Otherwise return the formatted string
        return sprintf(
            $this->format,
            $args['quarter'],
            $args['year']
        );
    }

    /**
     * Gets a four digit string year for copyright use throughout the product
     * @param string $version The standard software version string
     * @return string
     */
    public function getCopyrightYear(string $version) : string
    {
        // Get our methodology first
        $m = $this->getVersionMethodology($version);

        // If there is a year already, just use that
        if (!empty($m['args']['year'])) {
            return $this->getParsedYear($m['args']['year']);
        }

        // Otherwise, get the year from the version
        $m = $this->getVersionMeta($version);
        if (!empty($m['year'])) {
            return $this->getParsedYear($m['year']);
        }

        // Default is just the current year
        return date('Y');
    }

    /**
     * Returns a four digit year string made from a one or two digit year number
     * @param int $year The year to turn into a four digit year string
     * @return string
     */
    private function getParsedYear(int $year) : string
    {
        return date(
            'Y',
            strtotime(
                sprintf(
                    '01-Jun-%02d',
                    $year
                )
            )
        );
    }

    /**
     * Gets the methodology needed to handle versioning and such
     * @param string $version The version to use as a basis for calculation
     * @return array
     */
    private function getVersionMethodology(string $version) : array
    {
        foreach ($this->versionPatterns as $pattern => $actions) {
            if (preg_match("#$pattern#", $version)) {
                return [
                    'method' => $actions['method'],
                    'args' => $actions['args'] ?? $version,
                ];
            }
        }

        return [];
    }

    /**
     * Gets the formatted marketing version if one is found according to the
     * supported patterns, or the default value
     * @param string $version
     * @return string
     */
    public function getMarketingVersion(string $version) : string
    {
        if (($m = $this->getVersionMethodology($version)) !== []) {
            return $this->{$m['method']}($m['args']);
        }

        return $this->default;
    }

    /**
     * Gets the metadata for a version for use in forming the marketing version.
     * @param string $version
     * @return array|null
     */
    private function getVersionMeta(string $version) : ?array
    {
        $versionData = $this->getVersionData($version);
        return $versionData ?? null;
    }

    /**
     * Calculates a marketing version value based on the software version string
     * @param string $version
     * @return string
     */
    private function getCalculatedVersion(string $version) : string
    {
        if (($meta = $this->getVersionMeta($version)) !== null) {
            return $this->getFormattedVersion($meta);
        }

        return $this->default;
    }

    /**
     * Retrieves the marketing version metadata for a given version, based on
     * major/minor pairing
     * @param string $version
     * @return array
     */
    private function getVersionData(string $version) : array
    {
        // Grab the parts of the version
        list($major, $minor) = explode('.', $version);

        // We need integers
        $major = (int) $major;
        $minor = (int) $minor;

        // Since this scheme essentially started full swing with the 8.0 release
        // we should enforce that as a minimum version number for handling this
        if ($major < 8) {
            return [];
        }

        // Add 10 to it since our pattern is 8.x.x == Season '18 (until Winter)
        $year = $major + 10;

        // We rev the year when the minor octet is 3
        if ($minor === 3) {
            $year++;
        }

        // Maintain weirdness of incrementing years wrong,
        // like for Sugar version 91, which would be in the
        // year 2101.
        if ($year > 99) {
            $year -= 100;
        }

        return [
            'quarter' => $this->quarters[$minor],
            'year' => $year,
        ];
    }
}

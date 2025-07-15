<?php
/**
 * Utility functions for the MLS Listings Display plugin.
 * v4.0.0
 * - FEAT: Added get_field_label() and get_field_notes() to centralize specs.
 * - REFACTOR: render_grid_item now uses the new label function.
 */
class MLD_Utils {

    /**
     * Safely decodes a JSON string from the database.
     * @param string|null $json The JSON string.
     * @return array|null The decoded array or null.
     */
    public static function decode_json($json) {
        if (empty($json) || !is_string($json)) return null;
        $decoded = json_decode($json, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
    }

    /**
     * Formats a value for display, handling arrays, booleans, and empty values.
     * @param mixed $value The value to format.
     * @param string $na_string The string to return for empty values.
     * @return string The formatted, HTML-safe string.
     */
    public static function format_display_value($value, $na_string = 'N/A') {
        if (is_string($value) && (strpos(trim($value), '[') === 0 || strpos(trim($value), '{') === 0)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if (is_array($value)) {
            $filtered = array_filter($value, fn($item) => $item !== null && trim((string)$item) !== '');
            return empty($filtered) ? $na_string : esc_html(implode(', ', $filtered));
        }

        if (is_bool($value)) return $value ? 'Yes' : 'No';
        if ($value === null || trim((string)$value) === '' || trim((string)$value) === '[]') return $na_string;
        if (is_numeric($value)) {
            if ($value == 1) return 'Yes';
            if ($value == 0) return 'No';
        }
        if (is_string($value)) {
            $lower_value = strtolower(trim($value));
            if ($lower_value === 'yes') return 'Yes';
            if ($lower_value === 'no') return 'No';
        }

        return esc_html(trim((string)$value));
    }

    /**
     * Renders a grid item, now using the centralized get_field_label.
     * @param string $field_id The database field ID.
     * @param mixed $value The value to display.
     */
    public static function render_grid_item($field_id, $value) {
        $label = self::get_field_label($field_id);
        $formatted_value = self::format_display_value($value);

        if ($formatted_value !== 'N/A' && $formatted_value !== '') {
            echo '<div class="mld-grid-item"><strong>' . esc_html($label) . '</strong><span>' . $formatted_value . '</span></div>';
        }
    }

    /**
     * Gets the user-defined notes for a field.
     * @param string $field_id The database field ID.
     * @return string The note.
     */
    public static function get_field_notes($field_id) {
        $notes = [
            'BusinessType' => 'Used as PropertySubType When Property Type Bussiness Opportunity is Selected',
            'PrivateRemarks' => 'This field Should be visible to Admin Only',
            'ShowingInstructions' => 'This field Should be visible to Admin Only',
            'EntryLevel' => 'Add as a filter in the Modal',
            'LotSizeSquareFeet' => 'Add as filter in Modal',
            'StructureType' => 'Add as Dynamic Filter in Modal',
            'ArchitecturalStyle' => 'Add as Dynamic Filter in Modal',
            'PropertyAttachedYN' => 'Add a filter in the Modal',
            'SpaYN' => 'Add as checkbox in the Filters Modal',
            'WaterfrontYN' => 'Add as checkbox in the Filters Modal',
            'ViewYN' => 'Add as checkbox in the Filters Modal',
            'MLSPIN_WATERVIEW_FLAG' => 'Add as checkbox in the Filters Modal',
            'GarageSpaces' => 'Add as filter in the Modal',
            'ParkingTotal' => 'Add as filter in the Modal',
            'MLSPIN_LENDER_OWNED' => 'Add as checkbox in the Filters Modal',
            'MLSPIN_AvailableNow' => 'Add as checkbox in the Filters Modal right next the dave available filter',
            'OfficeRemarks' => 'This field Should be visible to Admin Only',
            'BuyerAgencyCompensation' => 'This field Should be visible to Admin Only',
            'MLSPIN_BUYER_COMP_OFFE' => 'This field Should be visible to Admin Only',
            'MLSPIN_SHOWINGS_DEFERR' => 'This field Should be visible to Admin Only',
            'MLSPIN_ALERT_COMMENTS' => 'This field Should be visible to Admin Only',
            'MLSPIN_DISCLOSURE' => 'This field Should be visible to Admin Only',
            'MLSPIN_COMP_BASED_ON' => 'This field Should be visible to Admin Only',
            'ListingExpirationDate' => 'This field Should be visible to Admin Only',
        ];
        return $notes[$field_id] ?? '';
    }

    /**
     * Gets the display label for a given database field ID.
     * @param string $field_id The database field ID.
     * @return string The formatted label.
     */
    public static function get_field_label($field_id) {
        static $labels = null;
        if ($labels === null) {
            $labels = [
                'ListingKey' => 'ListingKey',
                'ListingId' => 'MLS#',
                'ModificationTimestamp' => 'Last Updated',
                'CreationTimestamp' => 'Date Of Listings Creation',
                'StatusChangeTimestamp' => 'Status Change Date',
                'CloseDate' => 'Close Date',
                'PurchaseContractDate' => 'Date Under Contract',
                'ListingContractDate' => 'Listing agreement date',
                'OriginalEntryTimestamp' => 'List Date',
                'OffMarketDate' => 'Off Market Date',
                'StandardStatus' => 'Status',
                'MlsStatus' => 'MLS Status',
                'PropertyType' => 'Listing Type',
                'PropertySubType' => 'Property Type',
                'BusinessType' => 'Business Type',
                'ListPrice' => 'List Price',
                'OriginalListPrice' => 'Original List Price',
                'ClosePrice' => 'Sold Price',
                'PublicRemarks' => 'Description',
                'PrivateRemarks' => 'Private Remarks',
                'Disclosures' => 'Disclosures',
                'ShowingInstructions' => 'Showing Instructions',
                'UnparsedAddress' => 'Full Address',
                'StreetNumber' => 'Street number',
                'StreetDirPrefix' => 'Street prefix',
                'StreetName' => 'Street name',
                'StreetDirSuffix' => 'Street suffix',
                'StreetNumberNumeric' => 'Numeric street #',
                'UnitNumber' => 'Unit or Apt #',
                'EntryLevel' => 'Unit Level',
                'EntryLocation' => 'Unit Placement',
                'City' => 'City',
                'StateOrProvince' => 'State',
                'PostalCode' => 'Postal code',
                'PostalCodePlus4' => 'Fill Zip Code',
                'CountyOrParish' => 'County',
                'Country' => 'Country code',
                'MLSAreaMajor' => 'Area',
                'MLSAreaMinor' => 'Neighborhood',
                'SubdivisionName' => 'Subdivision',
                'Latitude' => 'Latitude',
                'Longitude' => 'Longitude',
                'Coordinates' => 'Geo point',
                'BedroomsTotal' => 'Bedrooms',
                'BathroomsTotalInteger' => 'Total baths',
                'BathroomsFull' => 'Full baths',
                'BathroomsHalf' => 'Half baths',
                'LivingArea' => 'Living Area',
                'AboveGradeFinishedArea' => 'Living Area Above Grade',
                'BelowGradeFinishedArea' => 'Living Area Below Grade',
                'LivingAreaUnits' => 'Living Area Units',
                'BuildingAreaTotal' => 'Building Area',
                'LotSizeAcres' => 'Lot size (acres)',
                'LotSizeSquareFeet' => 'Lot size (sq ft)',
                'LotSizeArea' => 'Lot size',
                'YearBuilt' => 'Year built',
                'YearBuiltEffective' => 'Effective year built',
                'YearBuiltDetails' => 'Notes on year built',
                'StructureType' => 'Structure type',
                'ArchitecturalStyle' => 'Style',
                'StoriesTotal' => 'Number of stories',
                'Levels' => 'Levels description',
                'PropertyAttachedYN' => 'Property Attached',
                'AttachedGarageYN' => 'Garage Attached',
                'Basement' => 'Basement details',
                'MLSPIN_MARKET_TIME_PROPERTY' => 'Days on market',
                'PropertyCondition' => 'Property Condition',
                'MLSPIN_COMPLEX_COMPLET' => 'Complex Complete',
                'MLSPIN_UNIT_BUILDING' => 'Unit building ID',
                'MLSPIN_COLOR' => 'Exterior color',
                'HomeWarrantyYN' => 'Home warranty',
                'ConstructionMaterials' => 'Materials used',
                'FoundationDetails' => 'Foundation type',
                'FoundationArea' => 'Foundation area',
                'Roof' => 'Roof',
                'Heating' => 'Heating system',
                'Cooling' => 'Cooling system',
                'Utilities' => 'Utilities',
                'Sewer' => 'Sewer type',
                'WaterSource' => 'Water source',
                'Electric' => 'Electric system',
                'ElectricOnPropertyYN' => 'Electricity on property?',
                'MLSPIN_COOLING_UNITS' => 'Number of cooling units',
                'MLSPIN_COOLING_ZONES' => 'Cooling zones',
                'MLSPIN_HEAT_ZONES' => 'Heat zones',
                'MLSPIN_HEAT_UNITS' => 'Heating units',
                'MLSPIN_HOT_WATER' => 'Hot water type',
                'MLSPIN_INSULATION_FEATUR' => 'Insulation details',
                'WaterSewerExpense' => 'Water/sewer expense',
                'ElectricExpense' => 'Electric expense',
                'InsuranceExpense' => 'Insurance expense',
                'InteriorFeatures' => 'Interior notes',
                'Flooring' => 'Flooring types',
                'Appliances' => 'Appliances',
                'FireplaceFeatures' => 'Fireplace features',
                'FireplacesTotal' => 'Fireplace count',
                'FireplaceYN' => 'Fireplace present?',
                'RoomsTotal' => 'Number of rooms',
                'WindowFeatures' => 'Window details',
                'DoorFeatures' => 'Door details',
                'LaundryFeatures' => 'Laundry notes',
                'SecurityFeatures' => 'Security systems',
                'SpaYN' => 'Spa Present',
                'SpaFeatures' => 'Spa Features',
                'ExteriorFeatures' => 'Exterior details',
                'PatioAndPorchFeatures' => 'Patio/porch details',
                'LotFeatures' => 'Lot details',
                'RoadSurfaceType' => 'Road surface type',
                'RoadFrontageType' => 'Road frontage type',
                'RoadResponsibility' => 'Road Responsibilty',
                'FrontageLength' => 'Frontage length',
                'FrontageType' => 'Frontage type',
                'Fencing' => 'Fencing details',
                'OtherStructures' => 'Other structures',
                'OtherEquipment' => 'Other equipment',
                'PastureArea' => 'Pasture area',
                'CultivatedArea' => 'Cultivated area',
                'WaterfrontYN' => 'Waterfront',
                'WaterfrontFeatures' => 'Waterfront features',
                'View' => 'View description',
                'ViewYN' => 'Has View',
                'CommunityFeatures' => 'Community features',
                'MLSPIN_WATERVIEW_FLAG' => 'Water view?',
                'MLSPIN_WATERVIEW_FEATUF' => 'Water view features',
                'GreenIndoorAirQuality' => 'Green air quality',
                'GreenEnergyGeneration' => 'Green energy generation',
                'HorseYN' => 'Horse property?',
                'HorseAmenities' => 'Horse amenities',
                'GarageSpaces' => 'Garage spaces',
                'GarageYN' => 'Garage present?',
                'CoveredSpaces' => 'Covered parking spaces',
                'ParkingTotal' => 'Non-garage parking spaces',
                'ParkingFeatures' => 'Parking features',
                'CarportYN' => 'Carport present?',
                'AssociationYN' => 'HOA present?',
                'AssociationFee' => 'HOA fee',
                'AssociationFeeFrequency' => 'HOA fee frequency',
                'AssociationName' => 'HOA name',
                'AssociationAmenities' => 'HOA amenities',
                'AssociationFeeIncludes' => 'HOA fee includes',
                'MLSPIN_OPTIONAL_FEE' => 'Optional HOA fee',
                'MLSPIN_OPT_FEE_INCLUDES' => 'Optional HOA fee includes',
                'MLSPIN_REQD_OWN_ASSOCI' => 'Ownership required?',
                'MLSPIN_NO_UNITS_OWNER' => 'Owner-occupied units',
                'MLSPIN_DPR_Flag' => 'Down payment resource eligible?',
                'MLSPIN_LENDER_OWNED' => 'Foreclosure',
                'GrossIncome' => 'Gross income',
                'GrossScheduledIncome' => 'Scheduled income',
                'NetOperatingIncome' => 'Net operating income',
                'OperatingExpense' => 'Operating expenses',
                'TotalActualRent' => 'Actual rent',
                'MLSPIN_SELLER_DISCOUNT' => 'Seller discount points',
                'FinancialDataSource' => 'Financial data source',
                'CurrentFinancing' => 'Current financing',
                'DevelopmentStatus' => 'Development status',
                'ExistingLeaseType' => 'Lease type',
                'AvailabilityDate' => 'Availability date',
                'MLSPIN_AvailableNow' => 'Available now?',
                'LeaseTerm' => 'Lease term',
                'RentIncludes' => 'Rent includes',
                'MLSPIN_SEC_DEPOSIT' => 'Security deposit',
                'MLSPIN_DEPOSIT_REQD' => 'Deposit required?',
                'MLSPIN_INSURANCE_REQD' => 'Insurance required?',
                'MLSPIN_LAST_MON_REQD' => 'Last month required?',
                'MLSPIN_FIRST_MON_REQD' => 'First month required?',
                'MLSPIN_REFERENCES_REQD' => 'References required?',
                'ElementarySchool' => 'Elementary school',
                'MiddleOrJuniorSchool' => 'Middle/junior school',
                'HighSchool' => 'High school',
                'SchoolDistrict' => 'School district',
                'Media' => 'Media assets',
                'PhotosCount' => 'Photo count',
                'VirtualTourURLUnbranded' => 'Unbranded tour URL',
                'VirtualTourURLBranded' => 'Branded tour URL',
                'ListAgentMlsId' => 'Listing agent ID',
                'BuyerAgentMlsId' => 'Buyer agent ID',
                'ListOfficeMlsId' => 'Listing office ID',
                'BuyerOfficeMlsId' => 'Buyer office ID',
                'MLSPIN_MAIN_SO' => 'Selling office ID',
                'MLSPIN_MAIN_LO' => 'Listing office ID',
                'MLSPIN_MSE' => 'Selling agent ID',
                'MLSPIN_MGF' => 'Buyer office ID',
                'MLSPIN_DEQE' => 'Buyer agent ID',
                'MLSPIN_SOLD_VS_RENT' => 'Sold or rented',
                'MLSPIN_TEAM_MEMBER' => 'Team member IDs',
                'OfficeRemarks' => 'Private Office Remarks',
                'BuyerAgencyCompensation' => 'Buyer compensation',
                'MLSPIN_BUYER_COMP_OFFE' => 'Buyer comp offered?',
                'MLSPIN_SHOWINGS_DEFERR' => 'Showings deferral date',
                'MLSPIN_ALERT_COMMENTS' => 'Alert comments',
                'MLSPIN_DISCLOSURE' => 'Disclosure info',
                'MLSPIN_COMP_BASED_ON' => 'Comp based on',
                'ListingExpirationDate' => 'Listing expiration',
                'TaxMapNumber' => 'Tax map number',
                'TaxBookNumber' => 'Tax book',
                'TaxBlock' => 'Tax block',
                'TaxLot' => 'Tax lot',
                'ParcelNumber' => 'Parcel number',
                'Zoning' => 'Zoning code',
                'ZoningDescription' => 'Zoning description',
                'MLSPIN_MASTER_PAGE' => 'Master deed page',
                'MLSPIN_MASTER_BOOK' => 'Master deed book',
                'MLSPIN_PAGE' => 'Deed page',
                'MLSPIN_SEWAGE_DISTRICT' => 'Sewage district',
                'ListAgentData' => 'Listing agent JSON',
                'ListOfficeData' => 'Listing office JSON',
                'BuyerAgentData' => 'Buyer agent JSON',
                'BuyerOfficeData' => 'Buyer office JSON',
                'OpenHouseData' => 'Open house JSON',
                'AdditionalData' => 'Extra data',
            ];
        }
        return $labels[$field_id] ?? ucwords(str_replace(['_', 'YN'], [' ', ''], preg_replace('/(?<!^)[A-Z]/', ' $0', $field_id)));
    }
}

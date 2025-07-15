<?php
/**
 * Template for displaying a single property listing.
 * v4.0.0
 * - REFACTOR: Complete overhaul based on new field specifications.
 * - FEAT: Renders dynamic sections and labels from MLD_Utils.
 * - FEAT: Hides fields not in the official list and respects admin-only visibility.
 */

// --- Main Template Logic ---
$mls_number = get_query_var('mls_number');
if (!$mls_number) {
    global $wp_query; $wp_query->set_404(); status_header(404); get_template_part(404); exit();
}

$listing = MLD_Query::get_listing_details($mls_number);

if (!$listing) {
    global $wp_query; $wp_query->set_404(); status_header(404); get_template_part(404); exit();
}

// --- Prepare Data for Display ---
$is_admin = is_user_logged_in() && current_user_can('manage_options');
$photos = MLD_Utils::decode_json($listing['Media']) ?: [];
$main_photo = !empty($photos) ? $photos[0]['MediaURL'] : 'https://placehold.co/1200x800/eee/ccc?text=No+Image';

$address_full = $listing['UnparsedAddress'] ?: trim(sprintf('%s, %s, %s %s', $listing['StreetNumber'], $listing['City'], $listing['StateOrProvince'], $listing['PostalCode']));
$price = '$' . number_format((float)$listing['ListPrice']);

$list_agent = MLD_Utils::decode_json($listing['ListAgentData']);
$list_office = MLD_Utils::decode_json($listing['ListOfficeData']);

// --- Define Section Structure from specs ---
$sections = [
    'Core Listing Details' => ['StandardStatus', 'MlsStatus', 'PropertyType', 'PropertySubType', 'BusinessType', 'ListPrice', 'OriginalListPrice', 'ClosePrice', 'PublicRemarks', 'PrivateRemarks', 'Disclosures', 'ShowingInstructions'],
    'Location Details' => ['UnparsedAddress', 'StreetNumber', 'StreetDirPrefix', 'StreetName', 'StreetDirSuffix', 'StreetNumberNumeric', 'UnitNumber', 'EntryLevel', 'EntryLocation', 'City', 'StateOrProvince', 'PostalCode', 'PostalCodePlus4', 'CountyOrParish', 'Country', 'MLSAreaMajor', 'MLSAreaMinor', 'SubdivisionName'],
    'Property Characteristics' => ['BedroomsTotal', 'BathroomsTotalInteger', 'BathroomsFull', 'BathroomsHalf', 'LivingArea', 'AboveGradeFinishedArea', 'BelowGradeFinishedArea', 'LivingAreaUnits', 'BuildingAreaTotal', 'LotSizeAcres', 'LotSizeSquareFeet', 'LotSizeArea', 'YearBuilt', 'YearBuiltEffective', 'YearBuiltDetails', 'StructureType', 'ArchitecturalStyle', 'StoriesTotal', 'Levels', 'PropertyAttachedYN', 'AttachedGarageYN', 'Basement', 'MLSPIN_MARKET_TIME_PROPERTY', 'PropertyCondition', 'MLSPIN_COMPLEX_COMPLET', 'MLSPIN_UNIT_BUILDING', 'MLSPIN_COLOR', 'HomeWarrantyYN'],
    'Construction & Utilities' => ['ConstructionMaterials', 'FoundationDetails', 'FoundationArea', 'Roof', 'Heating', 'Cooling', 'Utilities', 'Sewer', 'WaterSource', 'Electric', 'ElectricOnPropertyYN', 'MLSPIN_COOLING_UNITS', 'MLSPIN_COOLING_ZONES', 'MLSPIN_HEAT_ZONES', 'MLSPIN_HEAT_UNITS', 'MLSPIN_HOT_WATER', 'MLSPIN_INSULATION_FEATUR', 'WaterSewerExpense', 'ElectricExpense', 'InsuranceExpense'],
    'Interior Features' => ['InteriorFeatures', 'Flooring', 'Appliances', 'FireplaceFeatures', 'FireplacesTotal', 'FireplaceYN', 'RoomsTotal', 'WindowFeatures', 'DoorFeatures', 'LaundryFeatures', 'SecurityFeatures', 'SpaYN', 'SpaFeatures'],
    'Exterior & Lot Features' => ['ExteriorFeatures', 'PatioAndPorchFeatures', 'LotFeatures', 'RoadSurfaceType', 'RoadFrontageType', 'RoadResponsibility', 'FrontageLength', 'FrontageType', 'Fencing', 'OtherStructures', 'OtherEquipment', 'PastureArea', 'CultivatedArea', 'WaterfrontYN', 'WaterfrontFeatures', 'View', 'ViewYN', 'CommunityFeatures', 'MLSPIN_WATERVIEW_FLAG', 'MLSPIN_WATERVIEW_FEATUF', 'GreenIndoorAirQuality', 'GreenEnergyGeneration', 'HorseYN', 'HorseAmenities'],
    'Parking' => ['GarageSpaces', 'GarageYN', 'CoveredSpaces', 'ParkingTotal', 'ParkingFeatures', 'CarportYN'],
    'HOA & Financial' => ['AssociationYN', 'AssociationFee', 'AssociationFeeFrequency', 'AssociationName', 'AssociationAmenities', 'AssociationFeeIncludes', 'MLSPIN_OPTIONAL_FEE', 'MLSPIN_OPT_FEE_INCLUDES', 'MLSPIN_REQD_OWN_ASSOCI', 'MLSPIN_NO_UNITS_OWNER', 'MLSPIN_DPR_Flag', 'MLSPIN_LENDER_OWNED', 'GrossIncome', 'GrossScheduledIncome', 'NetOperatingIncome', 'OperatingExpense', 'TotalActualRent', 'MLSPIN_SELLER_DISCOUNT', 'FinancialDataSource', 'CurrentFinancing', 'DevelopmentStatus', 'ExistingLeaseType'],
    'Rental Specific' => ['AvailabilityDate', 'MLSPIN_AvailableNow', 'LeaseTerm', 'RentIncludes', 'MLSPIN_SEC_DEPOSIT', 'MLSPIN_DEPOSIT_REQD', 'MLSPIN_INSURANCE_REQD', 'MLSPIN_LAST_MON_REQD', 'MLSPIN_FIRST_MON_REQD', 'MLSPIN_REFERENCES_REQD'],
    'School Information' => ['ElementarySchool', 'MiddleOrJuniorSchool', 'HighSchool', 'SchoolDistrict'],
    'Municipal/Legal' => ['TaxMapNumber', 'TaxBookNumber', 'TaxBlock', 'TaxLot', 'ParcelNumber', 'Zoning', 'ZoningDescription', 'MLSPIN_MASTER_PAGE', 'MLSPIN_MASTER_BOOK', 'MLSPIN_PAGE', 'MLSPIN_SEWAGE_DISTRICT'],
];

get_header(); ?>

<div id="mld-single-property-page">
    <div class="mld-container">

        <!-- Header: Status, Address, Price -->
        <header class="mld-page-header">
            <div>
                <div class="mld-status-tags">
                    <span class="mld-status-tag primary"><?php echo esc_html($listing['StandardStatus']); ?></span>
                </div>
                <h1 class="mld-address-main"><?php echo esc_html($address_full); ?></h1>
            </div>
            <div class="mld-price-container">
                <div class="mld-price"><?php echo esc_html($price); ?></div>
            </div>
        </header>

        <!-- Gallery -->
        <div class="mld-gallery">
            <div class="mld-gallery-main-image">
                <img src="<?php echo esc_url($main_photo); ?>" alt="<?php echo esc_attr($address_full); ?>" id="mld-main-photo">
                <?php if (count($photos) > 1): ?>
                <button class="mld-slider-nav prev" aria-label="Previous image">&#10094;</button>
                <button class="mld-slider-nav next" aria-label="Next image">&#10095;</button>
                <?php endif; ?>
            </div>
            <?php if (count($photos) > 1): ?>
            <div class="mld-gallery-thumbnails">
                <?php foreach ($photos as $index => $photo): ?>
                    <img src="<?php echo esc_url($photo['MediaURL']); ?>" alt="Thumbnail <?php echo $index + 1; ?>" class="mld-thumb <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>" loading="lazy">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Main Content (2-column) -->
        <div class="mld-main-content-wrapper">
            <main class="mld-listing-details">
                
                <!-- Description -->
                <section class="mld-section">
                    <h2><?php echo MLD_Utils::get_field_label('PublicRemarks'); ?></h2>
                    <p class="mld-description"><?php echo nl2br(esc_html($listing['PublicRemarks'])); ?></p>
                </section>
                
                <!-- Admin-only Remarks -->
                <?php if ($is_admin): ?>
                    <?php
                    $admin_fields_to_show = ['PrivateRemarks', 'ShowingInstructions', 'OfficeRemarks', 'BuyerAgencyCompensation', 'MLSPIN_BUYER_COMP_OFFE', 'MLSPIN_SHOWINGS_DEFERR', 'MLSPIN_ALERT_COMMENTS', 'MLSPIN_DISCLOSURE', 'MLSPIN_COMP_BASED_ON', 'ListingExpirationDate'];
                    $admin_content = '';
                    foreach($admin_fields_to_show as $field_id) {
                        if (!empty($listing[$field_id])) {
                            $admin_content .= '<strong>' . MLD_Utils::get_field_label($field_id) . ':</strong> ' . esc_html($listing[$field_id]) . '<br>';
                        }
                    }
                    if (!empty($admin_content)) {
                        echo '<section class="mld-section"><div class="mld-admin-box info">' . $admin_content . '</div></section>';
                    }
                    ?>
                <?php endif; ?>

                <!-- Dynamic Detail Sections -->
                <?php
                foreach ($sections as $title => $fields) {
                    ob_start();
                    echo '<div class="mld-details-grid">';
                    foreach ($fields as $field_id) {
                        // Check if field should be visible
                        $is_admin_field = strpos(MLD_Utils::get_field_notes($field_id), 'Admin Only') !== false;

                        if ($is_admin_field) {
                            continue; // Skip admin fields in the public display sections
                        }

                        if (isset($listing[$field_id])) {
                            MLD_Utils::render_grid_item($field_id, $listing[$field_id]);
                        }
                    }
                    echo '</div>';
                    $section_html = ob_get_clean();

                    if (strpos($section_html, 'mld-grid-item') !== false) {
                        echo '<section class="mld-section">';
                        echo '<h2>' . esc_html($title) . '</h2>';
                        echo $section_html;
                        echo '</section>';
                    }
                }
                ?>
            </main>

            <!-- Sidebar -->
            <aside class="mld-sidebar">
                <div class="mld-sidebar-sticky-content">
                    <div class="mld-sidebar-card">
                        <button class="mld-sidebar-btn primary">Request a Tour</button>
                        <button class="mld-sidebar-btn secondary">Contact Agent</button>
                    </div>

                    <?php if ($list_agent || $list_office): ?>
                    <div class="mld-sidebar-card">
                        <p class="mld-sidebar-card-header">Listing Presented By</p>
                        <?php if ($list_agent): ?>
                        <div class="mld-agent-info">
                            <div class="mld-agent-avatar">
                                <?php echo esc_html(strtoupper(substr($list_agent['MemberFirstName'], 0, 1) . substr($list_agent['MemberLastName'], 0, 1))); ?>
                            </div>
                            <div class="mld-agent-details">
                                <strong><?php echo esc_html($list_agent['MemberFullName']); ?></strong>
                                <?php if (!empty($list_agent['MemberEmail'])): ?>
                                <a href="mailto:<?php echo esc_attr($list_agent['MemberEmail']); ?>">Email Agent</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($list_office): ?>
                        <div class="mld-office-info">
                            <strong><?php echo esc_html($list_office['OfficeName']); ?></strong>
                            <?php if (!empty($list_office['OfficePhone'])): ?>
                            <p>Office: <?php echo esc_html($list_office['OfficePhone']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</div>

<?php get_footer(); ?>

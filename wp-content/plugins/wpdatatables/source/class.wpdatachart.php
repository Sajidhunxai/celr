<?php

defined('ABSPATH') or die('Access denied.');

/**
 * Chart engine of wpDataTables plugin
 */
class WPDataChart
{

    private $_id = NULL;
    private $_wpdatatable_id = NULL;
    private $_engine = '';
    private $_type = '';
    private $_range_type = 'all_rows';
    private $_selected_columns = array();
    private $_row_range = array();
    private $_wpdatatable = NULL;
    // Chart
    private $_width = 400;
    private $_responsiveWidth = false;
    private $_height = 400;
    private $_group_chart = false;
    private $_background_color = '#FFFFFF';
    private $_border_width = 0;
    private $_border_color = '#4572A7';
    private $_border_radius = 0;
    private $_plot_background_color = 'undefined';
    private $_plot_border_width = 0;
    private $_plot_border_color = '#C0C0C0';
    private $_three_d = false;
    private $_font_size = NULL;
    private $_font_name = 'Arial';
    private $_font_weight = 'bold';
    private $_font_style = 'normal';
    private $_font_color = '#666';
    // Series
    private $_series = array();
    private $_curve_type = 'none';
    // Axes
    private $_show_grid = true;
    private $_axes = array(
        'major' => array(
            'label' => ''
        ),
        'minor' => array(
            'label' => ''
        )
    );
    private $_vertical_axis_min;
    private $_vertical_axis_max;
    private $_horizontal_axis_crosshair = false;
    private $_horizontal_axis_direction = 1;
    private $_vertical_axis_crosshair = false;
    private $_vertical_axis_direction = 1;
    private $_inverted = false;
    // Title
    private $_title = '';
    private $_show_title = true;
    private $_title_floating = false;
    private $_title_position = 'top';
    private $_title_font_name = 'Arial';
    private $_title_font_style = 'normal';
    private $_title_font_weight = 'bold';
    private $_title_font_color = '#666';
    // Tooltip
    private $_tooltip_enabled = true;
    private $_tooltip_shared = false;
    private $_tooltip_background_color = 'rgba(255, 255, 255, 0.85)';
    private $_tooltip_border_radius = 3;
    // Legend
    private $_legend_position = 'bottom';
    private $_legend_vertical_align = 'bottom';
    private $_show_legend = true;
    private $_legend_position_cjs = 'top';

    private $_user_defined_series_data = array();
    private $_render_data = NULL;
    private $_chartjs_render_data = NULL;

    private $_type_counters;

    public function __construct()
    {

    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getId()
    {
        return $this->_id;
    }

    // Chart

    public function setWidth($width)
    {
        $this->_width = $width;
    }

    public function getWidth()
    {
        return $this->_width;
    }

    public function isResponsiveWidth() {
        return $this->_responsiveWidth;
    }

    /**
     * @param boolean $responsiveWidth
     */
    public function setResponsiveWidth($responsiveWidth) {
        $this->_responsiveWidth = $responsiveWidth;
    }

    public function setHeight($height)
    {
        $this->_height = $height;
    }

    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * @param boolean $group_chart
     */
    public function setGroupChart($group_chart) {
        $this->_group_chart = $group_chart;
    }

    /**
     * @return boolean
     */
    public function isGroupChart() {
        return $this->_group_chart;
    }


    /**
     * @param $background_color
     */
    public function setBackgroundColor($background_color)
    {
        $this->_background_color = $background_color;
    }

    /**
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->_background_color;
    }

    /**
     * @param $border_width
     */
    public function setBorderWidth($border_width)
    {
        $this->_border_width = $border_width;
    }

    /**
     * @return int
     */
    public function getBorderWidth()
    {
        return $this->_border_width;
    }

    /**
     * @param $border_color
     */
    public function setBorderColor($border_color)
    {
        $this->_border_color = $border_color;
    }

    /**
     * @return string
     */
    public function getBorderColor()
    {
        return $this->_border_color;
    }

    /**
     * @param $border_radius
     */
    public function setBorderRadius($border_radius)
    {
        $this->_border_radius = $border_radius;
    }

    /**
     * @return int
     */
    public function getBorderRadius()
    {
        return $this->_border_radius;
    }


    /**
     * @param $plot_background_color
     */
    public function setPlotBackgroundColor($plot_background_color)
    {
        $this->_plot_background_color = $plot_background_color;
    }

    /**
     * @return string
     */
    public function getPlotBackgroundColor()
    {
        return $this->_plot_background_color;
    }


    /**
     * @param $plot_border_width
     */
    public function setPlotBorderWidth($plot_border_width)
    {
        $this->_plot_border_width = $plot_border_width;
    }

    /**
     * @return int
     */
    public function getPlotBorderWidth()
    {
        return $this->_plot_border_width;
    }

    /**
     * @param $plot_border_color
     */
    public function setPlotBorderColor($plot_border_color)
    {
        $this->_plot_border_color = $plot_border_color;
    }

    /**
     * @return string
     */
    public function getPlotBorderColor()
    {
        return $this->_plot_border_color;
    }

    /**
     * @return null
     */
    public function getFontSize() {
        return $this->_font_size;
    }

    /**
     * @param null $font_size
     */
    public function setFontSize($font_size) {
        $this->_font_size = $font_size;
    }

    /**
     * @return string
     */
    public function getFontName() {
        return $this->_font_name;
    }

    /**
     * @param string $font_name
     */
    public function setFontName($font_name) {
        $this->_font_name = $font_name;
    }

    /**
     * @return string
     */
    public function getFontWeight()
    {
        return $this->_font_weight;
    }

    /**
     * @param string $font_weight
     */
    public function setFontWeight($font_weight)
    {
        $this->_font_weight = $font_weight;
    }

    /**
     * @return string
     */
    public function getFontStyle() {
        return $this->_font_style;
    }

    /**
     * @param string $font_style
     */
    public function setFontStyle($font_style) {
        $this->_font_style = $font_style;
    }

    /**
     * @return string
     */
    public function getFontColor() {
        return $this->_font_color;
    }

    /**
     * @param string $font_color
     */
    public function setFontColor($font_color) {
        $this->_font_color = $font_color;
    }

    /**
     * @return string
     */
    public function getRealFontStyle()
    {
        if (isset($this->_chartjs_render_data['options']['globalOptions']['defaultFontStyle'])) {
            $oldFontStyle = $this->_chartjs_render_data['options']['globalOptions']['defaultFontStyle'];
            if (strpos($oldFontStyle, 'bold italic') !== false ||
                strpos($oldFontStyle, 'italic') !== false
            ) {
                return 'italic';
            }

            if (strpos($oldFontStyle, 'bold') !== false) {
                return 'normal';
            }
        }

        return $this->getFontStyle();
    }

    /**
     * @return string
     */
    public function getRealFontWeight()
    {
        if (isset($this->_chartjs_render_data['options']['globalOptions']['defaultFontStyle'])) {
            $oldFontStyle = $this->_chartjs_render_data['options']['globalOptions']['defaultFontStyle'];
            if (strpos($oldFontStyle, 'bold italic') !== false ||
                strpos($oldFontStyle, 'bold') !== false
            ) {
                return 'bold';
            }

            if (strpos($oldFontStyle, 'italic') !== false) {
                return 'normal';
            }
        }

        return $this->getFontWeight();
    }

    /**
     * @return string
     */
    public function getRealTitleFontStyle()
    {
        if (isset($this->_chartjs_render_data['options']['options']['title'])) {
            $oldTitleFontStyle = $this->_chartjs_render_data['options']['options']['title']['fontStyle'];
            if (strpos($oldTitleFontStyle, 'bold italic') !== false ||
                strpos($oldTitleFontStyle, 'italic') !== false
            ) {
                return 'italic';
            }

            if (strpos($oldTitleFontStyle, 'bold') !== false ||
                strpos($oldTitleFontStyle, 'normal') !== false
            ) {
                return 'normal';
            }
        }

        return $this->getTitleFontStyle();
    }

    /**
     * @return string
     */
    public function getRealTitleFontWeight()
    {
        if (isset($this->_chartjs_render_data['options']['options']['title'])) {
            $oldTitleFontStyle = $this->_chartjs_render_data['options']['options']['title']['fontStyle'];
            if (strpos($oldTitleFontStyle, 'bold italic') !== false ||
                strpos($oldTitleFontStyle, 'bold') !== false
            ) {
                return 'bold';
            }

            if (strpos($oldTitleFontStyle, 'italic') !== false ||
                strpos($oldTitleFontStyle, 'normal') !== false
            ) {
                return 'normal';
            }
        }

        return $this->getTitleFontWeight();
    }

    /**
     * @param boolean $three_d
     */
    public function setThreeD($three_d) {
        $this->_three_d = (bool)$three_d;
    }

    /**
     * @return boolean
     */
    public function isThreeD() {
        return $this->_three_d;
    }

    // Series

    /**
     * @param string $curve_type
     */
    public function setCurveType($curve_type) {
        $this->_curve_type = (bool)$curve_type;
    }

    /**
     * @return string
     */
    public function getCurveType() {
        return $this->_curve_type;
    }


    // Axes

    /**
     * @param $show_grid
     */
    public function setShowGrid($show_grid)
    {
        $this->_show_grid = (bool)$show_grid;
    }

    /**
     * @return bool
     */
    public function getShowGrid()
    {
        return $this->_show_grid;
    }

    /**
     * @param $label
     */
    public function setMajorAxisLabel($label)
    {
        $this->_axes['major']['label'] = $label;
    }

    /**
     * @return mixed
     */
    public function getMajorAxisLabel()
    {
        return $this->_axes['major']['label'];
    }


    /**
     * @param $label
     */
    public function setMinorAxisLabel($label)
    {
        $this->_axes['minor']['label'] = $label;
    }

    /**
     * @return mixed
     */
    public function getMinorAxisLabel()
    {
        return $this->_axes['minor']['label'];
    }

    /**
     * @param boolean $horizontal_axis_crosshair
     */
    public function setHorizontalAxisCrosshair($horizontal_axis_crosshair) {
        $this->_horizontal_axis_crosshair = (bool)$horizontal_axis_crosshair;
    }

    /**
     * @return boolean
     */
    public function isHorizontalAxisCrosshair() {
        return $this->_horizontal_axis_crosshair;
    }

    /**
     * @param int $horizontal_axis_direction
     */
    public function setHorizontalAxisDirection($horizontal_axis_direction) {
        $this->_horizontal_axis_direction = $horizontal_axis_direction;
    }

    /**
     * @return int
     */
    public function getHorizontalAxisDirection() {
        return $this->_horizontal_axis_direction;
    }

    /**
     * @param mixed $vertical_axis_min
     */
    public function setVerticalAxisMin($vertical_axis_min) {
        $this->_vertical_axis_min = $vertical_axis_min;
    }

    /**
     * @return mixed
     */
    public function getVerticalAxisMin() {
        return $this->_vertical_axis_min;
    }

    /**
     * @param mixed $vertical_axis_max
     */
    public function setVerticalAxisMax($vertical_axis_max) {
        $this->_vertical_axis_max = $vertical_axis_max;
    }

    /**
     * @return mixed
     */
    public function getVerticalAxisMax() {
        return $this->_vertical_axis_max;
    }

    /**
     * @param boolean $vertical_axis_crosshair
     */
    public function setVerticalAxisCrosshair($vertical_axis_crosshair) {
        $this->_vertical_axis_crosshair = (bool)$vertical_axis_crosshair;
    }

    /**
     * @return boolean
     */
    public function isVerticalAxisCrosshair() {
        return $this->_vertical_axis_crosshair;
    }

    /**
     * @param int $vertical_axis_direction
     */
    public function setVerticalAxisDirection($vertical_axis_direction) {
        $this->_vertical_axis_direction = $vertical_axis_direction;
    }

    /**
     * @return int
     */
    public function getVerticalAxisDirection() {
        return $this->_vertical_axis_direction;
    }

    /**
     * @param $inverted
     */
    public function setInverted($inverted) {
        $this->_inverted = (bool)$inverted;
    }

    /**
     * @return bool
     */
    public function isInverted() {
        return $this->_inverted;
    }

    // Title

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * @param boolean $show_title
     */
    public function setShowTitle($show_title)
    {
        $this->_show_title = $show_title;
    }

    /**
     * @return boolean
     */
    public function isShowTitle()
    {
        return $this->_show_title;
    }

    /**
     * @param boolean $title_floating
     */
    public function setTitleFloating($title_floating)
    {
        $this->_title_floating = (bool)$title_floating;
    }

    /**
     * @return boolean
     */
    public function isTitleFloating()
    {
        return $this->_title_floating;
    }

    /**
     * @return string
     */
    public function getTitlePosition() {
        return $this->_title_position;
    }

    /**
     * @param string $title_position
     */
    public function setTitlePosition($title_position) {
        $this->_title_position = $title_position;
    }

    /**
     * @return string
     */
    public function getTitleFontName() {
        return $this->_title_font_name;
    }

    /**
     * @param string $title_font_name
     */
    public function setTitleFontName($title_font_name) {
        $this->_title_font_name = $title_font_name;
    }

    /**
     * @return string
     */
    public function getTitleFontStyle() {
        return $this->_title_font_style;
    }

    /**
     * @param string $title_font_style
     */
    public function setTitleFontStyle($title_font_style) {
        $this->_title_font_style = $title_font_style;
    }

    /**
     * @return string
     */
    public function getTitleFontWeight()
    {
        return $this->_title_font_weight;
    }

    /**
     * @param string $title_font_weight
     */
    public function setTitleFontWeight($title_font_weight)
    {
        $this->_title_font_weight = $title_font_weight;
    }

    /**
     * @return string
     */
    public function getTitleFontColor() {
        return $this->_title_font_color;
    }

    /**
     * @param string $title_font_color
     */
    public function setTitleFontColor($title_font_color) {
        $this->_title_font_color = $title_font_color;
    }

    // Tooltip

    /**
     * @param boolean $tooltip_enabled
     */
    public function setTooltipEnabled($tooltip_enabled) {
        $this->_tooltip_enabled = (bool)$tooltip_enabled;
    }

    /**
     * @return boolean
     */
    public function isTooltipEnabled() {
        return $this->_tooltip_enabled;
    }

    /**
     * @param boolean $tooltip_shared
     */
    public function setTooltipShared($tooltip_shared) {
        $this->_tooltip_shared = (bool)$tooltip_shared;
    }

    /**
     * @return boolean
     */
    public function isTooltipShared() {
        return $this->_tooltip_shared;
    }

    /**
     * @param string $tooltip_background_color
     */
    public function setTooltipBackgroundColor($tooltip_background_color) {
        $this->_tooltip_background_color = $tooltip_background_color;
    }

    /**
     * @return string
     */
    public function getTooltipBackgroundColor() {
        return $this->_tooltip_background_color;
    }

    /**
     * @param int $tooltip_border_radius
     */
    public function setTooltipBorderRadius($tooltip_border_radius) {
        $this->_tooltip_border_radius = (int)$tooltip_border_radius;
    }

    /**
     * @return int
     */
    public function getTooltipBorderRadius() {
        return $this->_tooltip_border_radius;
    }

    // Legend


    /**
     * @return string
     */
    public function getLegendPosition()
    {
        return $this->_legend_position;
    }

    /**
     * @param string $legend_position
     */
    public function setLegendPosition($legend_position)
    {
        $this->_legend_position = $legend_position;
    }


    public function setLegendVerticalAlign($legend_vertical_align)
    {
        $this->_legend_vertical_align = $legend_vertical_align;
    }

    public function getLegendVerticalAlign()
    {
        return $this->_legend_vertical_align;
    }

    public function setShowLegend($show_legend) {
        $this->_show_legend = (bool)$show_legend;
    }

    public function getShowLegend() {
        return $this->_show_legend;
    }

    /**
     * @return string
     */
    public function getLegendPositionCjs() {
        return $this->_legend_position_cjs;
    }

    /**
     * @param string $legend_position_cjs
     */
    public function setLegendPositionCjs($legend_position_cjs) {
        $this->_legend_position_cjs = $legend_position_cjs;
    }


    /**
     * @param $series_data
     */
    public function setUserDefinedSeriesData($series_data)
    {
        if (is_array($series_data)) {
            $this->_user_defined_series_data = $series_data;
        }
    }

    /**
     * @return array
     */
    public function getUserDefinedSeriesData()
    {
        return $this->_user_defined_series_data;
    }


    /**
     * @param $engine
     */
    public function setEngine($engine)
    {
        $this->_engine = $engine;
    }

    /**
     * @return string
     */
    public function getEngine()
    {
        return $this->_engine;
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @param $row_range
     */
    public function setRowRange($row_range)
    {
        $this->_row_range = $row_range;
    }

    /**
     * @return array
     */
    public function getRowRange()
    {
        return $this->_row_range;
    }

    /**
     * @param $selected_columns
     */
    public function setSelectedColumns($selected_columns)
    {
        $this->_selected_columns = $selected_columns;
    }

    /**
     * @return array
     */
    public function getSelectedColumns()
    {
        return $this->_selected_columns;
    }

    /**
     * @param $wdt_id
     */
    public function setwpDataTableId($wdt_id)
    {
        $this->_wpdatatable_id = $wdt_id;
    }

    /**
     * @return null
     */
    public function getwpDataTableId()
    {
        return $this->_wpdatatable_id;
    }

    /**
     * @param $range_type
     */
    public function setRangeType($range_type)
    {
        $this->_range_type = $range_type;
    }

    /**
     * @return string
     */
    public function getRangeType()
    {
        return $this->_range_type;
    }

    /**
     * @param $constructedChartData
     * @param bool $loadFromDB
     * @return WPDataChart
     */
    public static function factory($constructedChartData, $loadFromDB = true)
    {
        $chartObj = new self();

        if (isset($constructedChartData['chart_id'])) {
            $chartObj->setId((int)$constructedChartData['chart_id']);
            if ($loadFromDB) {
                $chartObj->loadFromDB();
                $chartObj->prepareData();
                $chartObj->shiftStringColumnUp();
            }
        }

        // Main data (steps 1-3 of chart constructor)
        $chartObj->setwpDataTableId((int)$constructedChartData['wpdatatable_id']);
        $chartObj->setTitle(sanitize_text_field($constructedChartData['chart_title']));
        $chartObj->setEngine(sanitize_text_field($constructedChartData['chart_engine']));
        $chartObj->setType(sanitize_text_field($constructedChartData['chart_type']));
        $chartObj->setSelectedColumns(array_map('sanitize_text_field', $constructedChartData['selected_columns']));


        // Render data (step 4 or chart constructor)
        // Chart
        $chartObj->setWidth((int)WDTTools::defineDefaultValue($constructedChartData, 'width', 0));
        $chartObj->setResponsiveWidth((bool)WDTTools::defineDefaultValue($constructedChartData, 'responsive_width', 0));
        $chartObj->setHeight((int)WDTTools::defineDefaultValue($constructedChartData, 'height', 400));
        $chartObj->setGroupChart(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'group_chart', false)));
        $chartObj->setBackgroundColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'background_color', '#FFFFFF')));
        $chartObj->setBorderWidth(WDTTools::defineDefaultValue($constructedChartData, 'border_width', 0));
        $chartObj->setBorderColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'border_color', '#FFFFFF')));
        $chartObj->setBorderRadius((int)WDTTools::defineDefaultValue($constructedChartData, 'border_radius', 0));
        $chartObj->setPlotBackgroundColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'plot_background_color', '#FFFFFF')));
        $chartObj->setPlotBorderWidth((int)WDTTools::defineDefaultValue($constructedChartData, 'plot_border_width', 0));
        $chartObj->setPlotBorderColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'plot_border_color', '#C0C0C0')));
        $chartObj->setFontSize(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'font_size', null)));
        $chartObj->setFontName(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'font_name', 'Arial')));
        $chartObj->setFontStyle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'font_style', 'normal')));
        $chartObj->setFontWeight(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'font_weight', 'bold')));
        $chartObj->setFontColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'font_color', '#666')));
        $chartObj->setThreeD(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'three_d', false)));

        // Series
        if (!empty($constructedChartData['series_data'])) {
            array_walk_recursive(
                $constructedChartData['series_data'],
                function ($value, $key) {
                    sanitize_text_field($value);
                }
            );
            $chartObj->setUserDefinedSeriesData($constructedChartData['series_data']);
        }

        $chartObj->setCurveType(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'curve_type', 'none')));

        // Axes
        $chartObj->setShowGrid(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'show_grid', true)));
        $chartObj->setMajorAxisLabel(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'horizontal_axis_label', '')));
        $chartObj->setMinorAxisLabel(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_label', '')));
        $chartObj->setVerticalAxisMin(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_min', '')));
        $chartObj->setVerticalAxisMax(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_max', '')));
        $chartObj->setHorizontalAxisCrosshair(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'horizontal_axis_crosshair', false)));
        $chartObj->setHorizontalAxisDirection(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'horizontal_axis_direction', 1)));
        $chartObj->setVerticalAxisCrosshair(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_crosshair', false)));
        $chartObj->setVerticalAxisDirection(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'vertical_axis_direction', 1)));
        $chartObj->setInverted(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'inverted', false)));

        // Title
        $chartObj->setShowTitle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'show_title', true)));
        $chartObj->setTitleFloating(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_floating', false)));
        $chartObj->setTitlePosition(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_position', 'top')));
        $chartObj->setTitleFontName(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_font_name', 'Arial')));
        $chartObj->setTitleFontStyle(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_font_style', 'normal')));
        $chartObj->setTitleFontWeight(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_font_weight', 'bold')));
        $chartObj->setTitleFontColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'title_font_color', '#666')));

        // Tooltips
        $chartObj->setTooltipEnabled(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_enabled', true)));
        $chartObj->setTooltipBackgroundColor(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_background_color', 'rgba(255, 255, 255, 0.85)')));
        $chartObj->setTooltipBorderRadius((int)WDTTools::defineDefaultValue($constructedChartData, 'tooltip_border_radius', 3));
        $chartObj->setTooltipShared(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'tooltip_shared', false)));

        // Legend
        $chartObj->setLegendPosition(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_position', 'bottom')));
        $chartObj->setLegendVerticalAlign(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_vertical_align', 'bottom')));
        $chartObj->setShowLegend(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'show_legend', true)));
        $chartObj->setLegendPositionCjs(sanitize_text_field(WDTTools::defineDefaultValue($constructedChartData, 'legend_position_cjs', 'top')));



        $chartObj->loadChildWPDataTable();

        return $chartObj;
    }

    /**
     * @throws WDTException
     */
    public function loadChildWPDataTable()
    {
        if (empty($this->getwpDataTableId())) {
            throw new WDTException('Table ID does not exist for created chart.');
        }

        $this->_wpdatatable = WPDataTable::loadWpDataTable($this->getwpDataTableId(), null, empty($this->_follow_filtering));
    }

    public function shiftStringColumnUp()
    {
        /**
         * Check if the string column is not in the beginning and move it up
         */
        if (count($this->_render_data['columns']) > 1) {
            $shiftNeeded = false;
            $shiftIndex = 0;
            for ($i = 1; $i < count($this->_render_data['columns']); $i++) {
                if ($this->_render_data['columns'][$i]['type'] == 'string') {
                    $shiftNeeded = true;
                    $shiftIndex = $i;
                    break;
                }
            }

            if ($shiftNeeded) {
                // Shift columns
                $strColumn = $this->_render_data['columns'][$shiftIndex];
                unset($this->_render_data['columns'][$shiftIndex]);
                array_unshift($this->_render_data['columns'], $strColumn);
                // Shift rows
                for ($j = 0; $j < count($this->_render_data['rows']); $j++) {
                    $strCell = $this->_render_data['rows'][$j][$shiftIndex];
                    unset($this->_render_data['rows'][$j][$shiftIndex]);
                    array_unshift($this->_render_data['rows'][$j], $strCell);
                }
                // Shift column indexes
                if (isset($this->_render_data['column_indexes'])) {
                    $shiftedIndex = $this->_render_data['column_indexes'][$shiftIndex];
                    unset($this->_render_data['column_indexes'][$shiftIndex]);
                    array_unshift($this->_render_data['column_indexes'], $shiftedIndex);
                }
            }
        }

        // Format axes
        $this->_render_data['axes']['major'] = array(
            'type' => $this->_render_data['columns'][0]['type'],
            'label' => !empty($this->_render_data['hAxis']['title']) ?
                $this->_render_data['hAxis']['title'] : $this->_render_data['columns'][0]['label']
        );
        $this->_render_data['axes']['minor'] = array(
            'type' => $this->_render_data['columns'][1]['type'],
            'label' => !empty($this->_render_data['vAxis']['title']) ?
                $this->_render_data['vAxis']['title'] : ''
        );

        // Get all series names
        if (empty($this->_render_data['series'])) {
            for ($i = 1; $i < count($this->_render_data['columns']); $i++) {
                $this->_render_data['series'][] = array(
                    'label' => $this->_render_data['columns'][$i]['label'],
                    'color' => '',
                    'orig_header' => $this->_render_data['columns'][$i]['orig_header']
                );
            }
        }

    }

    public function prepareSeriesData()
    {
        // Init render data if it is empty
        if (empty($this->_render_data)) {
            $this->_render_data = array(
                'columns' => array(),
                'rows' => array(),
                'axes' => array(),
                'options' => array(
                    'title' => $this->_show_title ? $this->_title : '',
                    'series' => array(),
                    'width' => $this->_width,
                    'height' => $this->_height
                ),
                'vAxis' => array(),
                'hAxis' => array(),
                'errors' => array(),
                'series' => array()
            );
        }

        if ($this->isResponsiveWidth()) {
            unset($this->_render_data['options']['width']);
            $this->_render_data['options']['responsive_width'] = 1;
        }


        $this->_type_counters = array(
            'date' => 0,
            'datetime' => 0,
            'string' => 0,
            'number' => 0
        );

        // Define columns
        foreach ($this->getSelectedColumns() as $columnKey) {
            $columnType = $this->_wpdatatable->getColumn($columnKey)->getGoogleChartColumnType();
            $this->_render_data['columns'][] = array(
                'type' => $columnType,
                'label' => isset($this->_user_defined_series_data[$columnKey]['label']) ?
                    $this->_user_defined_series_data[$columnKey]['label'] : $this->_wpdatatable->getColumn($columnKey)->getTitle(),
                'orig_header' => $columnKey
            );
            $this->_type_counters[$columnType]++;
        }

        // Define axes titles
        if (isset($this->_axes['major']['label'])) {
            $this->_render_data['options']['hAxis']['title'] = $this->_axes['major']['label'];
        }
        if (isset($this->_axes['minor']['label'])) {
            $this->_render_data['options']['vAxis']['title'] = $this->_axes['minor']['label'];
        }

        // Define series colors,type and yaxis

        if (!empty($this->_user_defined_series_data)) {
            $seriesIndex = 0;
            foreach ($this->_user_defined_series_data as $series_data) {
                if (!empty($series_data['color']) || !empty($series_data['type'])) {
                    $this->_render_data['options']['series'][(int)$seriesIndex] = array(
                        'color' => $series_data['color'],
                        'label' => $series_data['label']
                    );
                }
                $seriesIndex++;
            }
        }

        // Group chart data
        if ($this->isGroupChart()) {
            $this->_render_data['group_chart'] = true;
        } else {
            $this->_render_data['group_chart'] = false;
        }


        // Define grid settings
        if (!$this->_show_grid) {
            if (!isset($this->_render_data['options']['hAxis'])) {
                $this->_render_data['options']['hAxis'] = array();
            }
            $this->_render_data['options']['hAxis']['gridlines'] = array(
                'color' => 'transparent'
            );
            if (!isset($this->_render_data['options']['vAxis'])) {
                $this->_render_data['options']['vAxis'] = array();
            }
            $this->_render_data['options']['vAxis']['gridlines'] = array(
                'color' => 'transparent'
            );
            $this->_render_data['show_grid'] = false;
        } else {
            $this->_render_data['show_grid'] = true;
        }

        // Detect errors
        if ($this->_type_counters['string'] > 1) {
            $this->_render_data['errors'][] = __('Only one column can be of type String', 'wpdatatables');
        }
        if (($this->_type_counters['number'] > 1) && ($this->_type_counters['date'] > 1)) {
            $this->_render_data['errors'][] = __('You are mixing data types (several date axes and several number)', 'wpdatatables');
        }

        if (empty($this->_highcharts_render_data)) {
            $this->_highcharts_render_data = array();
        }

    }

    public function groupData() {
        if (isset($this->_render_data['group_chart'])) {
            if ($this->isGroupChart() || $this->_render_data['group_chart'] == true) {
                $output = array();
                foreach ($this->_render_data['rows'] as $row) {
                    if (!empty($output)) {
                        $value_key = 'none';
                        foreach ($output as $key => $value) {
                            if ($value_key === 'none') {
                                if ($value[0] == $row[0]) {
                                    $value_key = $key;
                                }
                            }
                        }
                        if ($value_key === 'none') {
                            $output[] = $row;
                        } else {
                            for ($n = 1; $n <= count($row) - 1; $n++) {
                                $output[$value_key][$n] += $row[$n];
                            }
                        }
                    } else {
                        $output[] = $row;
                    }
                }
                $this->_group_chart = 1;
                $this->_render_data['rows'] = $output;
            } else {
                $this->_group_chart = 0;
            }
        } else {
            $this->_group_chart = 0;
        }

    }

    public function prepareGoogleChartsRender() {
        // Chart
        if (!$this->isResponsiveWidth()) {
            $this->_render_data['width'] = $this->getWidth();
        }
        $this->_render_data['width'] = $this->getWidth();
        $this->_render_data['options']['backgroundColor']['fill'] = $this->getBackgroundColor();
        $this->_render_data['options']['backgroundColor']['strokeWidth'] = $this->getBorderWidth();
        $this->_render_data['options']['backgroundColor']['stroke'] = $this->getBorderColor();
        $this->_render_data['options']['backgroundColor']['rx'] = $this->getBorderRadius();
        $this->_render_data['options']['chartArea']['backgroundColor']['fill'] = $this->getPlotBackgroundColor();
        $this->_render_data['options']['chartArea']['backgroundColor']['strokeWidth'] = $this->getPlotBorderWidth();
        $this->_render_data['options']['chartArea']['backgroundColor']['stroke'] = $this->getPlotBorderColor();
        $this->_render_data['options']['fontSize'] = $this->getFontSize();
        $this->_render_data['options']['fontName'] = $this->getFontName();
        if ($this->_type == 'google_pie_chart') {
            $this->_render_data['options']['is3D'] = $this->isThreeD();
        }

        // Series
        if ($this->_type == 'google_line_chart') {
            if ($this->getCurveType()) {
                $this->_render_data['options']['curveType'] = 'function';
            } else {
                $this->_render_data['options']['curveType'] = 'none';
            }
        }

        // Axes
        if ($this->isHorizontalAxisCrosshair() && !$this->isVerticalAxisCrosshair()) {
            $this->_render_data['options']['crosshair']['trigger'] = 'both';
            $this->_render_data['options']['crosshair']['orientation'] = 'horizontal';
        } elseif (!$this->isHorizontalAxisCrosshair() && $this->isVerticalAxisCrosshair()) {
            $this->_render_data['options']['crosshair']['trigger'] = 'both';
            $this->_render_data['options']['crosshair']['orientation'] = 'vertical';
        } elseif ($this->isHorizontalAxisCrosshair() && $this->isVerticalAxisCrosshair()) {
            $this->_render_data['options']['crosshair']['trigger'] = 'both';
            $this->_render_data['options']['crosshair']['orientation'] = 'both';
        } else {
            $this->_render_data['options']['crosshair']['trigger'] = '';
            $this->_render_data['options']['crosshair']['orientation'] = '';
        }
        $this->_render_data['options']['hAxis']['direction'] = $this->getHorizontalAxisDirection();
        $this->_render_data['options']['vAxis']['direction'] = $this->getVerticalAxisDirection();
        $this->_render_data['options']['vAxis']['viewWindow']['min'] = $this->getVerticalAxisMin();
        $this->_render_data['options']['vAxis']['viewWindow']['max'] = $this->getVerticalAxisMax();
        if ($this->isInverted()) {
            $this->_render_data['options']['orientation'] = 'vertical';
        } else {
            $this->_render_data['options']['orientation'] = 'horizontal';
        }

        // Title
        if ($this->isTitleFloating()) {
            $this->_render_data['options']['titlePosition'] = 'in';
        } else {
            $this->_render_data['options']['titlePosition'] = 'out';
        }

        // Tooltip
        if ($this->isTooltipEnabled()) {
            $this->_render_data['options']['tooltip']['trigger'] = 'focus';
        } else {
            $this->_render_data['options']['tooltip']['trigger'] = 'none';
        }


        // Legend
        $this->_render_data['options']['legend']['position'] = $this->getLegendPosition();
        if ($this->getLegendVerticalAlign() == 'bottom') {
            $this->_render_data['options']['legend']['alignment'] = 'end';
        } elseif ($this->getLegendVerticalAlign() == 'middle') {
            $this->_render_data['options']['legend']['alignment'] = 'center';
        } else {
            $this->_render_data['options']['legend']['alignment'] = 'start';
        }

        $this->_render_data = apply_filters('wpdatatables_filter_google_charts_render_data', $this->_render_data, $this->getId(), $this);
    }

    /**
     * Prepare ChartJS Data and Options
     */
    public function prepareChartJSRender()
    {

        $seriesEntry = array();

        $chartToSetOptionBeginAtZero = array(
            'chartjs_pie_chart',
            'chartjs_radar_chart',
            'chartjs_doughnut_chart',
            'chartjs_polar_area_chart',
            'chartjs_bubble_chart'
        );

        $colors = array(
            '#ff6384',
            '#36a2eb',
            '#ffce56',
            '#4bc0c0',
            '#9966ff',
            '#ff9f40',
            '#a6cee3',
            '#6a3d9a',
            '#b15928',
            '#fb9a99',
            '#0476e8',
            '#49C172',
            '#EA5E57',
            '#FFF458',
            '#BFEB54',
        );

        if ($this->_render_data['series']) {
            if (!empty($this->_chartjs_render_data['options']['data']['datasets'])) {
                $this->_chartjs_render_data['options']['data']['datasets'] = array();
            }
            if (in_array(
                $this->_type,
                array(
                    'chartjs_line_chart',
                    'chartjs_area_chart',
                    'chartjs_stacked_area_chart',
                    'chartjs_column_chart',
                    'chartjs_stacked_column_chart',
                    'chartjs_bar_chart',
                    'chartjs_stacked_bar_chart',
                    'chartjs_radar_chart'
                )
            )) {
                // Series and Categories
                for ($i = 0; $i < count($this->_render_data['columns']); $i++) {
                    if ($i == 0) {
                        $this->_chartjs_render_data['options']['data']['labels'] = array();
                    } else {
                        $seriesEntry = array(
                            'label' => $this->_render_data['series'][$i - 1]['label'],
                            'orig_header' => $this->_render_data['series'][$i - 1]['orig_header'],
                            'backgroundColor' => isset($this->_render_data['options']['series'][$i - 1]) ?
                                WDTTools::hex2rgba($this->_render_data['options']['series'][$i - 1]['color'], 0.2) : WDTTools::hex2rgba($colors[($i - 1) % 10], 0.2),
                            'borderColor' => isset($this->_render_data['options']['series'][$i - 1]) ?
                                $this->_render_data['options']['series'][$i - 1]['color'] : $colors[($i - 1) % 10],
                            'borderWidth' => 1,
                            'data' => array(),
                            'lineTension' => ($this->getCurveType()) ? 0.4 : 0,
                            'fill' => $this->_type != 'chartjs_line_chart'
                        );
                    }
                    foreach ($this->_render_data['rows'] as $row) {
                        if ($i == 0) {
                            $this->_chartjs_render_data['options']['data']['labels'][] = $row[$i];
                        } else {
                            $seriesEntry['data'][] = $row[$i];
                        }
                    }
                    if ($i != 0) {
                        $this->_chartjs_render_data['options']['data']['datasets'][] = $seriesEntry;
                    }
                }
            } else if (in_array(
                $this->_type,
                array(
                    'chartjs_polar_area_chart',
                    'chartjs_pie_chart',
                    'chartjs_doughnut_chart'
                )
            )) {
                // Series and Categories
                for ($i = 0; $i < count($this->_render_data['columns']); $i++) {
                    if ($i == 0) {
                        $this->_chartjs_render_data['options']['data']['labels'] = array();
                    } else {
                        $seriesEntry = array(
                            'label' => $this->_render_data['series'][$i - 1]['label'],
                            'backgroundColor' => isset($this->_render_data['options']['series'][$i - 1]) ?
                                $this->_render_data['options']['series'][$i - 1]['color'] : $colors,
                            'borderWidth' => 1,
                            'data' => array()
                        );
                    }
                    foreach ($this->_render_data['rows'] as $row) {
                        if ($i == 0) {
                            $this->_chartjs_render_data['options']['data']['labels'][] = $row[$i];
                        } else {
                            $seriesEntry['data'][] = $row[$i];
                        }
                    }
                    if ($i != 0) {
                        $this->_chartjs_render_data['options']['data']['datasets'][] = $seriesEntry;
                    }
                }
            } else if ($this->_type == 'chartjs_bubble_chart') {
                // Series and Categories
                for ($i = 0; $i < 2; $i++) {
                    foreach ($this->_render_data['rows'] as $key => $row) {
                        if ($i == 0) {
                            $this->_chartjs_render_data['options']['data']['datasets'][$key]['label'] = $row[$i];
                        }
                        if ($i == 1) {
                            $seriesEntry = array(
                                'data' => array()
                            );
                            $seriesEntry['backgroundColor'] = $colors[$key % 10];
                            $seriesEntry['hoverBackgroundColor'] = $colors[$key % 10];
                            $seriesEntry['data'][0]['x'] = $row[$i];
                            $seriesEntry['data'][0]['y'] = $row[$i + 1];
                            $seriesEntry['data'][0]['r'] = $row[$i + 2];
                            $this->_chartjs_render_data['options']['data']['datasets'][$key] = array_merge($this->_chartjs_render_data['options']['data']['datasets'][$key], $seriesEntry);
                        }
                    }
                }
            }
        }


        // Chart
        $this->_chartjs_render_data['configurations']['type'] = $this->getType();
        $this->_chartjs_render_data['configurations']['container']['height'] = $this->getHeight();
        if ($this->isResponsiveWidth()) {
            $this->_chartjs_render_data['configurations']['container']['width'] = 0;
            $this->_chartjs_render_data['options']['options']['maintainAspectRatio'] = true;
        } else {
            $this->_chartjs_render_data['configurations']['container']['width'] = $this->getWidth();
            $this->_chartjs_render_data['options']['options']['maintainAspectRatio'] = false;
        }
        $this->_chartjs_render_data['configurations']['canvas']['backgroundColor'] = $this->getBackgroundColor();
        $this->_chartjs_render_data['configurations']['canvas']['borderWidth'] = $this->getBorderWidth();
        $this->_chartjs_render_data['configurations']['canvas']['borderColor'] = $this->getBorderColor();
        $this->_chartjs_render_data['configurations']['canvas']['borderRadius'] = $this->getBorderRadius();
        $this->_chartjs_render_data['options']['globalOptions']['font']['size'] = $this->getFontSize() != '' ? (int)$this->getFontSize() : 12;
        $this->_chartjs_render_data['options']['globalOptions']['font']['family'] = $this->getFontName();
        $this->_chartjs_render_data['options']['globalOptions']['font']['style'] = $this->getRealFontStyle();
        $this->_chartjs_render_data['options']['globalOptions']['font']['weight'] = $this->getRealFontWeight();
        $this->_chartjs_render_data['options']['globalOptions']['color'] = $this->getFontColor() == '' ? '#666' : $this->getFontColor();

        // Axes
        if (!$this->_show_grid) {
            $this->_chartjs_render_data['options']['options']['scales']['x']['display'] = false;
            $this->_chartjs_render_data['options']['options']['scales']['y']['display'] = false;
        }
        $this->_chartjs_render_data['options']['options']['scales']['x']['title']['display'] = true;
        $this->_chartjs_render_data['options']['options']['scales']['x']['title']['text'] = $this->getMajorAxisLabel();
        $this->_chartjs_render_data['options']['options']['scales']['x']['title']['font']['size'] = $this->getFontSize() != '' ? (int)$this->getFontSize() : 12;
        $this->_chartjs_render_data['options']['options']['scales']['x']['title']['font']['weight'] = $this->getRealFontWeight();
        $this->_chartjs_render_data['options']['options']['scales']['x']['title']['font']['style'] = $this->getRealFontStyle();
        $this->_chartjs_render_data['options']['options']['scales']['x']['title']['font']['family'] = $this->getFontName();
        $this->_chartjs_render_data['options']['options']['scales']['x']['title']['color'] = $this->getFontColor() == '' ? '#666' : $this->getFontColor();
        $this->_chartjs_render_data['options']['options']['scales']['y']['title']['display'] = true;
        $this->_chartjs_render_data['options']['options']['scales']['y']['title']['text'] = $this->getMinorAxisLabel();
        $this->_chartjs_render_data['options']['options']['scales']['y']['title']['font']['size'] = $this->getFontSize() != '' ? (int)$this->getFontSize() : 12;
        $this->_chartjs_render_data['options']['options']['scales']['y']['title']['font']['weight'] = $this->getRealFontWeight();
        $this->_chartjs_render_data['options']['options']['scales']['y']['title']['font']['style'] = $this->getRealFontStyle();
        $this->_chartjs_render_data['options']['options']['scales']['y']['title']['font']['family'] = $this->getFontName();
        $this->_chartjs_render_data['options']['options']['scales']['y']['title']['color'] = $this->getFontColor() == '' ? '#666' : $this->getFontColor();
        if ($this->getVerticalAxisMin() != '') {
            $this->_chartjs_render_data['options']['options']['scales']['y']['min'] = intval($this->getVerticalAxisMin());
        } else {
            if (in_array($this->_type, $chartToSetOptionBeginAtZero)) {
                $this->_chartjs_render_data['options']['options']['scales']['y']['beginAtZero'] = true;
                $this->_chartjs_render_data['options']['options']['scales']['y']['min'] = 0;
            } else {
                $this->_chartjs_render_data['options']['options']['scales']['y']['beginAtZero'] = false;
            }
        }
        $this->_chartjs_render_data['options']['options']['scales']['y']['ticks']['color'] = $this->getFontColor() == '' ? '#666' : $this->getFontColor();
        $this->_chartjs_render_data['options']['options']['scales']['y']['ticks']['font']['size'] = $this->getFontSize() != '' ? (int)$this->getFontSize() : 12;
        $this->_chartjs_render_data['options']['options']['scales']['y']['ticks']['font']['weight'] = $this->getRealFontWeight();
        $this->_chartjs_render_data['options']['options']['scales']['y']['ticks']['font']['style'] = $this->getRealFontStyle();
        $this->_chartjs_render_data['options']['options']['scales']['y']['ticks']['font']['family'] = $this->getFontName();
        $this->_chartjs_render_data['options']['options']['scales']['x']['ticks']['color'] = $this->getFontColor() == '' ? '#666' : $this->getFontColor();
        $this->_chartjs_render_data['options']['options']['scales']['x']['ticks']['font']['size'] = $this->getFontSize() != '' ? (int)$this->getFontSize() : 12;
        $this->_chartjs_render_data['options']['options']['scales']['x']['ticks']['font']['weight'] = $this->getRealFontWeight();
        $this->_chartjs_render_data['options']['options']['scales']['x']['ticks']['font']['style'] = $this->getRealFontStyle();
        $this->_chartjs_render_data['options']['options']['scales']['x']['ticks']['font']['family'] = $this->getFontName();
        if (in_array($this->_type, array('chartjs_polar_area_chart', 'chartjs_radar_chart'))) {
            $this->_chartjs_render_data['options']['options']['scales']['r']['ticks']['font']['size'] = $this->getFontSize() != '' ? (int)$this->getFontSize() : 12;
            $this->_chartjs_render_data['options']['options']['scales']['r']['ticks']['font']['weight'] = $this->getRealFontWeight();
            $this->_chartjs_render_data['options']['options']['scales']['r']['ticks']['font']['style'] = $this->getRealFontStyle();
            $this->_chartjs_render_data['options']['options']['scales']['r']['ticks']['font']['family'] = $this->getFontName();
            $this->_chartjs_render_data['options']['options']['scales']['r']['ticks']['color'] = $this->getFontColor() == '' ? '#666' : $this->getFontColor();

            $this->_chartjs_render_data['options']['options']['scales']['r']['pointLabels']['font']['size'] = $this->getFontSize() != '' ? (int)$this->getFontSize() : 12;
            $this->_chartjs_render_data['options']['options']['scales']['r']['pointLabels']['font']['weight'] = $this->getRealFontWeight();
            $this->_chartjs_render_data['options']['options']['scales']['r']['pointLabels']['font']['style'] = $this->getRealFontStyle();
            $this->_chartjs_render_data['options']['options']['scales']['r']['pointLabels']['font']['family'] = $this->getFontName();
            $this->_chartjs_render_data['options']['options']['scales']['r']['pointLabels']['color'] = $this->getFontColor() == '' ? '#666' : $this->getFontColor();
        }
        if ($this->getVerticalAxisMax() != '') {
            $this->_chartjs_render_data['options']['options']['scales']['y']['max'] = intval($this->getVerticalAxisMax());
        }

        // Title
        if ($this->_show_title) {
            $this->_chartjs_render_data['options']['options']['plugins']['title']['display'] = true;
            $this->_chartjs_render_data['options']['options']['plugins']['title']['text'] = $this->getTitle();
        } else {
            $this->_chartjs_render_data['options']['options']['plugins']['title']['display'] = false;
        }
        $this->_chartjs_render_data['options']['options']['plugins']['title']['position'] = $this->getTitlePosition();
        $this->_chartjs_render_data['options']['options']['plugins']['title']['font']['family'] = $this->getTitleFontName();
        $this->_chartjs_render_data['options']['options']['plugins']['title']['font']['weight'] = $this->getRealTitleFontWeight();
        $this->_chartjs_render_data['options']['options']['plugins']['title']['font']['style'] = $this->getRealTitleFontStyle();
        $this->_chartjs_render_data['options']['options']['plugins']['title']['font']['size'] = $this->getFontSize() != '' ? (int)$this->getFontSize() : 12;
        $this->_chartjs_render_data['options']['options']['plugins']['title']['color'] = ($this->getTitleFontColor() != '') ? $this->getTitleFontColor() : '#666';

        // Tooltip
        $this->_chartjs_render_data['options']['options']['plugins']['tooltip']['enabled'] = $this->isTooltipEnabled();
        if ($this->isTooltipShared()) {
            $this->_chartjs_render_data['options']['options']['plugins']['tooltip']['mode'] = 'index';
        } else {
            $this->_chartjs_render_data['options']['options']['plugins']['tooltip']['mode'] = 'nearest';
            $this->_chartjs_render_data['options']['options']['plugins']['tooltip']['intersect'] = true;
        }
        $this->_chartjs_render_data['options']['options']['plugins']['tooltip']['backgroundColor'] = strpos($this->getTooltipBackgroundColor(), 'rgba') !== false ? $this->getTooltipBackgroundColor() : WDTTools::hex2rgba($this->getTooltipBackgroundColor(), 0.8);
        $this->_chartjs_render_data['options']['options']['plugins']['tooltip']['cornerRadius'] = $this->getTooltipBorderRadius();
        $this->_chartjs_render_data['options']['options']['plugins']['tooltip']['titleFont']['size'] = 12;
        $this->_chartjs_render_data['options']['options']['plugins']['tooltip']['bodyFont']['size'] = 12;
        $this->_chartjs_render_data['options']['options']['plugins']['tooltip']['footerFont']['size'] = 12;

        // Legend
        $this->_chartjs_render_data['options']['options']['plugins']['legend']['display'] = $this->getShowLegend();
        $this->_chartjs_render_data['options']['options']['plugins']['legend']['position'] = $this->getLegendPositionCjs();
        $this->_chartjs_render_data['options']['options']['plugins']['legend']['labels']['color'] = $this->getFontColor() == '' ? '#666' : $this->getFontColor();;
        $this->_chartjs_render_data['options']['options']['plugins']['legend']['labels']['font']['size'] = $this->getFontSize() != '' ? (int)$this->getFontSize() : 12;;
        $this->_chartjs_render_data['options']['options']['plugins']['legend']['labels']['font']['weight'] = $this->getRealFontWeight();
        $this->_chartjs_render_data['options']['options']['plugins']['legend']['labels']['font']['style'] = $this->getRealFontStyle();

        // Compatibility with Chartjs 4.x version for old charts
        if (isset($this->_chartjs_render_data['options']['options']['title']))
            unset($this->_chartjs_render_data['options']['options']['title']);
        if (isset($this->_chartjs_render_data['options']['options']['legend']))
            unset($this->_chartjs_render_data['options']['options']['legend']);
        if (isset($this->_chartjs_render_data['options']['options']['tooltips']))
            unset($this->_chartjs_render_data['options']['options']['tooltips']);
        if (isset($this->_chartjs_render_data['options']['options']['scales']['xAxes']))
            unset($this->_chartjs_render_data['options']['options']['scales']['xAxes']);
        if (isset($this->_chartjs_render_data['options']['options']['scales']['yAxes']))
            unset($this->_chartjs_render_data['options']['options']['scales']['yAxes']);
        if (isset($this->_chartjs_render_data['options']['globalOptions']['defaultFontSize']))
            unset($this->_chartjs_render_data['options']['globalOptions']['defaultFontSize']);
        if (isset($this->_chartjs_render_data['options']['globalOptions']['defaultFontFamily']))
            unset($this->_chartjs_render_data['options']['globalOptions']['defaultFontFamily']);
        if (isset($this->_chartjs_render_data['options']['globalOptions']['defaultFontStyle']))
            unset($this->_chartjs_render_data['options']['globalOptions']['defaultFontStyle']);
        if (isset($this->_chartjs_render_data['options']['globalOptions']['defaultFontColor']))
            unset($this->_chartjs_render_data['options']['globalOptions']['defaultFontColor']);
        if (isset($this->_chartjs_render_data['options']['globalOptions']['defaultFontColor']))
            unset($this->_chartjs_render_data['options']['globalOptions']['defaultFontColor']);
        if (isset($this->_chartjs_render_data['options']['title']['fontFamily']))
            unset($this->_chartjs_render_data['options']['title']['fontFamily']);
        if (isset($this->_chartjs_render_data['options']['title']['fontStyle']))
            unset($this->_chartjs_render_data['options']['title']['fontStyle']);
        if (isset($this->_chartjs_render_data['options']['title']['fontColor']))
            unset($this->_chartjs_render_data['options']['title']['fontColor']);

        $this->_chartjs_render_data = apply_filters('wpdatatables_filter_chartjs_render_data', $this->_chartjs_render_data, $this->getId(), $this);

    }

    /**
     * Prepares the data for Google charts format
     */
    public function prepareData()
    {

        // Prepare series and columns
        if (empty($this->_render_data['columns'])) {
            $this->prepareSeriesData();
        }

        $dateFormat = ($this->getEngine() == 'google') ? DateTime::RFC2822 : get_option('wdtDateFormat');
        $timeFormat = get_option('wdtTimeFormat');

        // The data itself
        if (empty($this->_render_data['rows'])) {
            if ($this->getRangeType() == 'all_rows') {
                foreach ($this->_wpdatatable->getDataRows() as $row) {
                    $return_data_row = array();
                    foreach ($this->getSelectedColumns() as $columnKey) {
                        $decimalPlaces =$this->_wpdatatable->getColumn($columnKey)->getDecimalPlaces();
                        $dataType = $this->_wpdatatable->getColumn($columnKey)->getDataType();
                        $thousandsSeparator = $this->_wpdatatable->getColumn($columnKey)->isShowThousandsSeparator();
                        switch ($dataType) {
                            case 'date':
                                $timestamp = is_int($row[$columnKey]) ? $row[$columnKey] : strtotime(str_replace('/', '-', $row[$columnKey]));
                                $return_data_row[] = date(
                                    $dateFormat,
                                    $timestamp
                                );
                                break;
                            case 'datetime':
                                $timestamp = is_int($row[$columnKey]) ? $row[$columnKey] : strtotime(str_replace('/', '-', $row[$columnKey]));
                                if ($this->getEngine() == 'google') {
                                    $return_data_row[] = date(
                                        $dateFormat,
                                        $timestamp
                                    );
                                } else {
                                    $return_data_row[] = date(
                                        $dateFormat . ' ' . $timeFormat,
                                        $timestamp
                                    );
                                }
                                break;
                            case 'time':
                                $timestamp = $row[$columnKey];
                                $return_data_row[] = date(
                                    $timeFormat,
                                    $timestamp
                                );
                                break;
                            case 'int':
                                if (has_filter('wpdatatables_filter_int_cell_data_in_charts')) {
                                    $row[$columnKey] = apply_filters('wpdatatables_filter_int_cell_data_in_charts', $row[$columnKey], $columnKey, $this->getId(), $this->_wpdatatable->getWpId());
                                    if (!is_null($row[$columnKey])) {
                                        $return_data_row[] = (float)$row[$columnKey];
                                    } else {
                                        $return_data_row[] = null;
                                    }
                                } else {
                                    $return_data_row[] = (float)$row[$columnKey];
                                }
                                break;
                            case 'float':
                                if (has_filter('wpdatatables_filter_float_cell_data_in_charts')) {
                                    $row[$columnKey] = apply_filters('wpdatatables_filter_float_cell_data_in_charts', $row[$columnKey], $columnKey, $this->getId(), $this->_wpdatatable->getWpId());
                                    if (!is_null($row[$columnKey])) {
                                        if ($decimalPlaces != -1) {
                                            $return_data_row[] = (float)number_format(
                                                (float)($row[$columnKey]),
                                                $decimalPlaces,
                                                '.',
                                                $thousandsSeparator ? '' : '.');
                                        } else {
                                            $return_data_row[] = (float)$row[$columnKey];
                                        }
                                    } else {
                                        $return_data_row[] = null;
                                    }
                                } else {
                                    if ($decimalPlaces != -1){
                                        $return_data_row[] = (float)number_format(
                                            (float)($row[$columnKey]),
                                            $decimalPlaces,
                                            '.',
                                            $thousandsSeparator ? '' : '.');
                                    }else {
                                        $return_data_row[] = (float)$row[$columnKey];
                                    }
                                }
                                break;
                            case 'string':
                            default:
                                $return_data_row[] = $row[$columnKey];
                                break;
                        }
                    }
                    $this->_render_data['rows'][] = $return_data_row;
                }
            } else {
                foreach ($this->_row_range as $rowIndex) {
                    $return_data_row = array();
                    foreach ($this->getSelectedColumns() as $columnKey) {

                        $dataType = $this->_wpdatatable->getColumn($columnKey)->getDataType();
                        $decimalPlaces =$this->_wpdatatable->getColumn($columnKey)->getDecimalPlaces();
                        switch ($dataType) {
                            case 'date':
                                $timestamp = is_int($this->_wpdatatable->getCell($columnKey, $rowIndex)) ?
                                    $this->_wpdatatable->getCell($columnKey, $rowIndex)
                                    : strtotime(str_replace('/', '-', $this->_wpdatatable->getCell($columnKey, $rowIndex)));
                                $return_data_row[] = date(
                                    $dateFormat,
                                    $timestamp
                                );
                                break;
                            case 'datetime':
                                $timestamp = is_int($this->_wpdatatable->getCell($columnKey, $rowIndex)) ?
                                    $this->_wpdatatable->getCell($columnKey, $rowIndex) : strtotime(str_replace('/', '-', $this->_wpdatatable->getCell($columnKey, $rowIndex)));
                                if ($this->getEngine() == 'google') {
                                    $return_data_row[] = date(
                                        $dateFormat,
                                        $timestamp
                                    );
                                } else {
                                    $return_data_row[] = date(
                                        $dateFormat . ' ' . $timeFormat,
                                        $timestamp
                                    );
                                }
                                break;
                            case 'time':
                                $timestamp = $this->_wpdatatable->getCell($columnKey, $rowIndex);
                                $return_data_row[] = date(
                                    $timeFormat,
                                    $timestamp
                                );
                                break;
                            case 'int':
                                if (has_filter('wpdatatables_filter_int_cell_data_in_charts')) {
                                    $cellData = apply_filters('wpdatatables_filter_int_cell_data_in_charts', $this->_wpdatatable->getCell($columnKey, $rowIndex), $columnKey, $this->getId(), $this->_wpdatatable->getWpId());
                                    if (!is_null($cellData)) {
                                        $return_data_row[] = (float)$cellData;
                                    } else {
                                        $return_data_row[] = null;
                                    }
                                }else {
                                    $return_data_row[] = (float)$this->_wpdatatable->getCell($columnKey, $rowIndex);
                                }
                                break;
                            case 'float':
                                if (has_filter('wpdatatables_filter_float_cell_data_in_charts')) {
                                    $floatNumber = apply_filters('wpdatatables_filter_float_cell_data_in_charts', $this->_wpdatatable->getCell($columnKey, $rowIndex), $columnKey , $this->getId(), $this->_wpdatatable->getWpId());
                                    if (!is_null($floatNumber)) {
                                        if ($decimalPlaces != -1) {
                                            $return_data_row[] = (float)number_format($floatNumber, $decimalPlaces);
                                        } else {
                                            $return_data_row[] = $floatNumber;
                                        }
                                    } else {
                                        $return_data_row[] = null;
                                    }
                                } else {
                                    $floatNumber= (float)$this->_wpdatatable->getCell($columnKey, $rowIndex);;
                                    if ($decimalPlaces != -1){
                                        $return_data_row[] = (float)number_format ($floatNumber, $decimalPlaces);
                                    } else {
                                        $return_data_row[] = $floatNumber;
                                    }
                                }
                                break;
                            case 'link':
                                $cellData = $this->_wpdatatable->getCell($columnKey, $rowIndex);
                                if (!in_array($this->getEngine(),['google','chartjs'])) {
                                    if (strpos($cellData, '||') !== false) {
                                        list($link, $cellData) = explode('||', $cellData);
                                        $return_data_row[] = '<a href="' . $link . '">' . $cellData . '</a>';
                                    } else {
                                        $return_data_row[] = '<a href="' . $cellData . '">' . $cellData . '</a>';
                                    }
                                } else {
                                    $return_data_row[] = $cellData;
                                }
                                break;
                            case 'string':
                            default:
                                $return_data_row[] = $this->_wpdatatable->getCell($columnKey, $rowIndex);
                                break;
                        }

                    }
                    $this->_render_data['rows'][] = $return_data_row;
                }
            }

        }

        $this->_render_data['type'] = $this->_type;
        return $this->_render_data;
    }

    public function returnGoogleChartData()
    {
        $this->prepareData();
        $this->groupData();
        $this->shiftStringColumnUp();
        $this->prepareGoogleChartsRender();
        return $this->_render_data;
    }

    public function returnChartJSData() {
        $this->prepareData();
        $this->groupData();
        $this->shiftStringColumnUp();
        $this->prepareChartJSRender();
        return $this->_chartjs_render_data;
    }


    public function returnData()
    {
        if ($this->getEngine() == 'google') {
            return $this->returnGoogleChartData();
        } else if ($this->getEngine() == 'chartjs') {
            return $this->returnChartJSData();
        }
    }


    /**
     * Saves the chart data to DB
     * @global WPDB $wpdb
     */
    public function save()
    {
        global $wpdb;

        $this->prepareSeriesData();
        $this->shiftStringColumnUp();

        switch ($this->getEngine()) {
            case 'google':
                $this->prepareGoogleChartsRender();
                break;
            case 'chartjs':
                $this->prepareChartJSRender();
                break;
        }


        $render_data = array(
            'selected_columns' => $this->getSelectedColumns(),
            'range_type' => $this->getRangeType(),
            'row_range' => $this->getRowRange(),
            'render_data' => $this->_render_data,
            'chartjs_render_data' => $this->_chartjs_render_data,
            'show_grid' => $this->_show_grid,
            'show_title' => $this->_show_title
        );


        if (empty($this->_id)) {
            // This is a new chart

            $wpdb->insert(
                $wpdb->prefix . "wpdatacharts",
                array(
                    'wpdatatable_id' => $this->getwpDataTableId(),
                    'title' => $this->getTitle(),
                    'engine' => $this->getEngine(),
                    'type' => $this->_type,
                    'json_render_data' => json_encode($render_data)
                )
            );

            $this->_id = $wpdb->insert_id;

        } else {
            // Updating the chart
            $wpdb->update(
                $wpdb->prefix . "wpdatacharts",
                array(
                    'wpdatatable_id' => $this->getwpDataTableId(),
                    'title' => $this->_title,
                    'engine' => $this->_engine,
                    'type' => $this->_type,
                    'json_render_data' => json_encode($render_data)
                ),
                array(
                    'id' => $this->_id
                )
            );

        }

    }

    public function getColumnIndexes()
    {
        foreach ($this->getSelectedColumns() as $columnKey) {
            $this->_render_data['column_indexes'][] = $this->_wpdatatable->getColumnHeaderOffset($columnKey);
        }
    }

    /**
     * Return the shortcode
     */
    public function getShortCode()
    {
        if (!empty($this->_id)) {
            return '[wpdatachart id=' . $this->_id . ']';
        } else {
            return '';
        }
    }

    /**
     * Load from DB
     * @return bool
     */
    public function loadFromDB()
    {
        global $wpdb;

        if (empty($this->_id)) {
            return false;
        }

        // Load json data from DB
        $chartQuery = $wpdb->prepare(
            "SELECT * 
                        FROM " . $wpdb->prefix . "wpdatacharts 
                        WHERE id = %d",
            $this->_id
        );
        $chartData = $wpdb->get_row($chartQuery);

        if ($chartData === null) {
            return false;
        }

        $renderData = json_decode($chartData->json_render_data, true);
        $this->_render_data = $renderData['render_data'];

        if ($chartData->engine == 'chartjs') {
            if (!empty($renderData['chartjs_render_data'])) {
                $this->_chartjs_render_data = $renderData['chartjs_render_data'];
            }
        }

        $this->setTitle($chartData->title);
        $this->setEngine($chartData->engine);
        $this->setwpDataTableId($chartData->wpdatatable_id);
        $this->setType($chartData->type);
        $this->setSelectedColumns($renderData['selected_columns']);
        $this->setRangeType($renderData['range_type']);
        $this->setRowRange($renderData['row_range']);
        $this->setShowGrid(isset($renderData['show_grid']) ? $renderData['show_grid'] : false);
        $this->setShowTitle(isset($renderData['show_title']) ? $renderData['show_title'] : false);
        $this->setResponsiveWidth(isset($renderData['render_data']['options']['responsive_width']) ? (bool)$renderData['render_data']['options']['responsive_width'] : false);
        if (!empty($renderData['render_data']['options']['width'])) {
            $this->setWidth($renderData['render_data']['options']['width']);
        }
        $this->setHeight($renderData['render_data']['options']['height']);

        if ($chartData->engine == 'google') {
            // Chart
            $this->setBackgroundColor(isset($renderData['render_data']['options']['backgroundColor']['fill']) ? $renderData['render_data']['options']['backgroundColor']['fill'] : '');
            $this->setBorderWidth(isset($renderData['render_data']['options']['backgroundColor']['strokeWidth']));
            $this->setBorderColor(isset($renderData['render_data']['options']['backgroundColor']['stroke']));
            $this->setBorderRadius(isset($renderData['render_data']['options']['backgroundColor']['rx']));
            $this->setPlotBackgroundColor(isset($renderData['render_data']['options']['chartArea']['backgroundColor']['fill']));
            $this->setPlotBorderWidth(isset($renderData['render_data']['options']['chartArea']['backgroundColor']['strokeWidth']));
            $this->setPlotBorderColor(isset($renderData['render_data']['options']['chartArea']['backgroundColor']['stroke']) ? $renderData['render_data']['options']['chartArea']['backgroundColor']['stroke'] : '');

            // Series
            if ($this->_type == 'google_line_chart') {
                $this->setCurveType(isset($renderData['render_data']['options']['curveType']));
            }

            // Axes
            if ($this->isHorizontalAxisCrosshair() && !$this->isVerticalAxisCrosshair()) {
                $renderData['render_data']['options']['crosshair']['trigger'] = 'both';
                $renderData['render_data']['options']['crosshair']['orientation'] = 'horizontal';
            } elseif (!$this->isHorizontalAxisCrosshair() && $this->isVerticalAxisCrosshair()) {
                $renderData['render_data']['options']['crosshair']['trigger'] = 'both';
                $renderData['render_data']['options']['crosshair']['orientation'] = 'vertical';
            } elseif ($this->isHorizontalAxisCrosshair() && $this->isVerticalAxisCrosshair()) {
                $renderData['render_data']['options']['crosshair']['trigger'] = 'both';
                $renderData['render_data']['options']['crosshair']['orientation'] = 'both';
            }
            $this->setHorizontalAxisDirection($renderData['render_data']['options']['hAxis']);
            $this->setVerticalAxisDirection($renderData['render_data']['options']['vAxis']);
            $this->setVerticalAxisMin(isset($renderData['render_data']['options']['vAxis']['viewWindow']['min']));
            $this->setVerticalAxisMax(isset($renderData['render_data']['options']['vAxis']['viewWindow']['max']));
            if ($this->isInverted()) {
                $renderData['render_data']['options']['orientation'] = 'vertical';
            } else {
                $renderData['render_data']['options']['orientation'] = 'horizontal';
            }

            // Title
            if ($this->isTitleFloating()) {
                $renderData['render_data']['options']['titlePosition'] = 'in';
            } else {
                $renderData['render_data']['options']['titlePosition'] = 'out';
            }

            // Tooltip
            if ($this->isTooltipEnabled()) {
                $renderData['render_data']['options']['tooltip']['trigger'] = 'focus';
            } else {
                $renderData['render_data']['options']['tooltip']['trigger'] = 'none';
            }

            // Legend
            $this->setLegendPosition(isset($renderData['render_data']['options']['legend']['position']));
            $this->setLegendVerticalAlign(isset($renderData['render_data']['options']['legend']['alignment']));
        } else if ($chartData->engine == 'chartjs') {
            // Chart
            $this->setBackgroundColor($renderData['chartjs_render_data']['configurations']['canvas']['backgroundColor']);
            $this->setBorderWidth($renderData['chartjs_render_data']['configurations']['canvas']['borderWidth']);
            $this->setBorderColor($renderData['chartjs_render_data']['configurations']['canvas']['borderColor']);
            $this->setBorderRadius($renderData['chartjs_render_data']['configurations']['canvas']['borderRadius']);
            if (isset($renderData['chartjs_render_data']['options']['globalOptions']['defaultFontSize'])) {
                $this->setFontSize($renderData['chartjs_render_data']['options']['globalOptions']['defaultFontSize']);
            } else {
                $this->setFontSize($renderData['chartjs_render_data']['options']['globalOptions']['font']['size']);
            }
            if (isset($renderData['chartjs_render_data']['options']['globalOptions']['defaultFontFamily'])) {
                $this->setFontName($renderData['chartjs_render_data']['options']['globalOptions']['defaultFontFamily']);
            } else {
                $this->setFontName($renderData['chartjs_render_data']['options']['globalOptions']['font']['family']);
            }
            if (isset($renderData['chartjs_render_data']['options']['globalOptions']['defaultFontStyle'])) {
                $this->setFontStyle($renderData['chartjs_render_data']['options']['globalOptions']['defaultFontStyle']);
            } else {
                $this->setFontStyle($renderData['chartjs_render_data']['options']['globalOptions']['font']['style']);
                $this->setFontWeight($renderData['chartjs_render_data']['options']['globalOptions']['font']['weight']);
            }
            if (isset($renderData['chartjs_render_data']['options']['globalOptions']['defaultFontColor'])) {
                $this->setFontColor($renderData['chartjs_render_data']['options']['globalOptions']['defaultFontColor']);
            } else {
                $this->setFontColor($renderData['chartjs_render_data']['options']['globalOptions']['color']);
            }
            // Series
            isset($renderData['chartjs_render_data']['options']['data']['datasets'][0]['lineTension']) ?
                $this->setCurveType($renderData['chartjs_render_data']['options']['data']['datasets'][0]['lineTension']) : null;
            // Axes
            if (isset($renderData['chartjs_render_data']['options']['options']['scales']['xAxes'][0]['scaleLabel']['labelString'])) {
                $this->setMajorAxisLabel($renderData['chartjs_render_data']['options']['options']['scales']['xAxes'][0]['scaleLabel']['labelString']);
            } else {
                $this->setMajorAxisLabel($renderData['chartjs_render_data']['options']['options']['scales']['x']['title']['text']);
            }
            if (isset($renderData['chartjs_render_data']['options']['options']['scales']['yAxes'][0]['scaleLabel']['labelString'])) {
                $this->setMinorAxisLabel($renderData['chartjs_render_data']['options']['options']['scales']['yAxes'][0]['scaleLabel']['labelString']);
            } else {
                $this->setMinorAxisLabel($renderData['chartjs_render_data']['options']['options']['scales']['y']['title']['text']);
            }
            if (isset($renderData['chartjs_render_data']['options']['options']['scales']['yAxes'][0]['ticks']['min']))
                $this->setVerticalAxisMin($renderData['chartjs_render_data']['options']['options']['scales']['yAxes'][0]['ticks']['min']);
            if (isset($renderData['chartjs_render_data']['options']['options']['scales']['yAxes'][0]['ticks']['max']))
                $this->setVerticalAxisMax($renderData['chartjs_render_data']['options']['options']['scales']['yAxes'][0]['ticks']['max']);
            if (isset($renderData['chartjs_render_data']['options']['options']['scales']['y']['min']))
                $this->setVerticalAxisMin($renderData['chartjs_render_data']['options']['options']['scales']['y']['min']);
            if (isset($renderData['chartjs_render_data']['options']['options']['scales']['y']['max']))
                $this->setVerticalAxisMax($renderData['chartjs_render_data']['options']['options']['scales']['y']['max']);

            if (isset($renderData['chartjs_render_data']['options']['options']['plugins'])) {
                // Title
                $this->setTitlePosition($renderData['chartjs_render_data']['options']['options']['plugins']['title']['position']);
                $this->setTitleFontName($renderData['chartjs_render_data']['options']['options']['plugins']['title']['font']['family']);
                $this->setTitleFontStyle($renderData['chartjs_render_data']['options']['options']['plugins']['title']['font']['style']);
                $this->setTitleFontWeight($renderData['chartjs_render_data']['options']['options']['plugins']['title']['font']['weight']);
                $this->setTitleFontColor($renderData['chartjs_render_data']['options']['options']['plugins']['title']['color']);
                // Tooltip
                $this->setTooltipEnabled($renderData['chartjs_render_data']['options']['options']['plugins']['tooltip']['enabled']);
                if ($renderData['chartjs_render_data']['options']['options']['plugins']['tooltip']['mode'] == 'nearest') {
                    $this->setTooltipShared(false);
                } else {
                    $this->setTooltipShared(true);
                }
                $this->setTooltipBackgroundColor($renderData['chartjs_render_data']['options']['options']['plugins']['tooltip']['backgroundColor']);
                $this->setTooltipBorderRadius($renderData['chartjs_render_data']['options']['options']['plugins']['tooltip']['cornerRadius']);
                // Legend
                $this->setShowLegend($renderData['chartjs_render_data']['options']['options']['plugins']['legend']['display']);
                $this->setLegendPositionCjs($renderData['chartjs_render_data']['options']['options']['plugins']['legend']['position']);
            } else {
                // Title
                $this->setTitlePosition($renderData['chartjs_render_data']['options']['options']['title']['position']);
                $this->setTitleFontName($renderData['chartjs_render_data']['options']['options']['title']['fontFamily']);
                $this->setTitleFontStyle($renderData['chartjs_render_data']['options']['options']['title']['fontStyle']);
                $this->setTitleFontColor($renderData['chartjs_render_data']['options']['options']['title']['fontColor']);
                // Tooltip
                $this->setTooltipEnabled($renderData['chartjs_render_data']['options']['options']['tooltips']['enabled']);
                if ($renderData['chartjs_render_data']['options']['options']['tooltips']['mode'] == 'single') {
                    $this->setTooltipShared(false);
                } else {
                    $this->setTooltipShared(true);
                }
                $this->setTooltipBackgroundColor($renderData['chartjs_render_data']['options']['options']['tooltips']['backgroundColor']);
                $this->setTooltipBorderRadius($renderData['chartjs_render_data']['options']['options']['tooltips']['cornerRadius']);
                // Legend
                $this->setShowLegend($renderData['chartjs_render_data']['options']['options']['legend']['display']);
                $this->setLegendPositionCjs($renderData['chartjs_render_data']['options']['options']['legend']['position']);
            }

        }


        $this->loadChildWPDataTable();

    }

    /**
     * Render Chart
     */
    public function renderChart()
    {
        $minified_js = get_option('wdtMinifiedJs');

        $this->prepareData();

        $this->groupData();

        $this->shiftStringColumnUp();

        $js_ext = $minified_js ? '.min.js' : '.js';
        if (!(is_admin() && function_exists('register_block_type')) ||
            (is_admin() && get_current_screen()->base == 'wpdatatables_page_wpdatatables-constructor')
        ) {
            wp_enqueue_script('wpdatatables-render-chart', WDT_JS_PATH . 'wpdatatables/wdt.chartsRender' . $js_ext, array('jquery'), WDT_CURRENT_VERSION);
        }

        if ($this->_engine == 'google') {
            // Google Chart JS
            wp_enqueue_script('wdt-google-charts', '//www.gstatic.com/charts/loader.js', array(), WDT_CURRENT_VERSION);
            wp_enqueue_script('wpdatatables-google-chart', WDT_JS_PATH . 'wpdatatables/wdt.googleCharts' . $js_ext, array('jquery'), WDT_CURRENT_VERSION);
            $json_chart_render_data = json_encode($this->_render_data);
        } else if ($this->_engine == 'chartjs') {
            $this->prepareChartJSRender();
            wp_enqueue_script('wdt-chartjs', WDT_JS_PATH . 'chartjs/Chart.js', array(), WDT_CURRENT_VERSION);
            // ChartJS wpDataTable JS library
            wp_enqueue_script('wpdatatables-chartjs', WDT_JS_PATH . 'wpdatatables/wdt.chartJS' . $js_ext, array('jquery'), WDT_CURRENT_VERSION);
            $json_chart_render_data = json_encode($this->_chartjs_render_data);
        }

        do_action('wdt-enqueue-scripts-after-chart-render');


        $chart_id = $this->_id;
        ob_start();
        include(WDT_TEMPLATE_PATH . 'wpdatachart.inc.php');
        $chart_html = ob_get_contents();
        ob_end_clean();
        return $chart_html;

    }

    /**
     * Return render data
     */
    public function getRenderData()
    {
        return $this->_render_data;
    }

    /**
     * Return ChartJS render data
     * @return null
     */
    public function getChartJSRenderData() {
        return $this->_chartjs_render_data;
    }

    /**
     * Delete chart by ID
     * @param $chartId
     * @return bool
     */
    public static function deleteChart($chartId)
    {
        global $wpdb;

        if (!isset($_REQUEST['wdtNonce']) || empty($chartId) || !current_user_can('manage_options') || !wp_verify_nonce($_REQUEST['wdtNonce'], 'wdtDeleteChartNonce')) {
            return false;
        }

        $wpdb->delete(
            $wpdb->prefix . "wpdatacharts",
            array(
                'id' => (int)$chartId
            )
        );

        return true;

    }

    /**
     * Get all charts non-paged for the MCE editor
     * @return array|null|object
     */
    public static function getAllCharts()
    {
        global $wpdb;
        $query = "SELECT id, title FROM {$wpdb->prefix}wpdatacharts ";
        $allCharts = $wpdb->get_results($query, ARRAY_A);
        return $allCharts;
    }

}

?>

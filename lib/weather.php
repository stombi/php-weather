<?php

/**
 * Weather
 * 
 * PHP 5 wrapper for Yahoo! weather.
 * 
 * @author Christopher Pitt
 */
class Weather
{
    const METRIC = 0;
    const IMPERIAL = 1;
    
    const JSON = 0;
    const SERIALIZED = 1;
    
    const TEMPERATURE = 0;
    const DISTANCE = 1;
    const PRESSURE = 2;
    
    protected $metric_units = array(
        'F' => 'C',
        'mi' => 'km',
        'in' => 'mb',
        'mph' => 'kph'
    );
    
    protected $wind_direction = array(
        'N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW', 'N'
    );
    
    protected $pressure_direction = array(
        'steady', 'rising', 'falling'
    );
    
    /**
     * Weather::__construct()
     * 
     * @param bool $metric Metric units of measurement must be used.
     * @param integer $cache_minutes Duration for which the weather data must be cached.
     * @param string $cache_path Folder in which to place the cache files (needs read/write permissions).
     * @param int $cache_format Format in which the cached data will be saved (JSON/SERIALIZED).
     */
    public function __construct($metric = true, $cache_minutes = 60, $cache_path = 'cache/', $cache_format = JSON)
    {
        $this->metric = $metric;
        $this->cache_minutes = $cache_minutes;
        $this->cache_path = $cache_path;
        $this->cache_format = $cache_format;
    }
    
    /**
     * Weather::get()
     * 
     * This method will return the weather data for the specified area.
     * 
     * @param mixed $zip US zip code or area code (weather.com).
     * @return array Weather data for requested area.
     */
    public function get($zip)
    {
        $prefix = 'weather_';
        $system = $this->metric ? 'metric_' : 'imperial_';
        $format = ($this->cache_format == JSON) ? '.json' : '.serialized';
        
        $weather_data;
        $file_path = strtolower($this->cache_path . $prefix . $system . $zip . $format);
        if ($this->cache_minutes && file_exists($file_path) && ((filemtime($file_path) + ($this->cache_minutes * 60)) > time()))
        {
            if ($this->cache_format == JSON)
            {
                $weather_data = json_decode(file_get_contents($file_path));
            }
            else
            {
                $weather_data = unserialize(file_get_contents($file_path));
            }
        }
        else
        {
            $weather_data = $this->process($zip);
            
            if ($this->cache_format == JSON)
            {
                file_put_contents($file_path, json_encode($weather_data));
            }
            else
            {
                file_put_contents($file_path, serialize($weather_data));
            }
        }
        
        return $this->objects($weather_data);
    }
    
    /**
     * Weather::__construct()
     * 
     * @param mixed $object Object to walk and convert from array to object.
     * @param mixed Either an object converted from an array, or the original value.
     */
    protected function objects($object)
    {
        if (is_array($object))
        {
            foreach ($object as $key => $value)
            {
                $object[$key] = $this->objects($value);
            }
            
            return (object) $object;
        }
        
        return $object;
    }
    
    /**
     * Weather::process()
     * 
     * This method will process the weather data of a specified area into an array.
     * 
     * @param mixed $zip US zip code or area code (weather.com).
     * @return array Weather data for requested area.
     */
    public function process($zip)
    {        
        $xml = new DOMDocument();
        $xml->loadXML($this->get_url('http://xml.weather.yahoo.com/forecastrss?p=' . $zip));
        
        $yahoo_ns = 'http://xml.weather.yahoo.com/ns/rss/1.0';
        $geo_ns = 'http://www.w3.org/2003/01/geo/wgs84_pos#';
        
        $language = $xml->getElementsByTagName('language')->item(0)->nodeValue;
        
        $location_node = $xml->getElementsByTagNameNS($yahoo_ns, 'location')->item(0);
        $location = array(
            'city' => $location_node->attributes->getNamedItem('city')->value,
            'region' => $location_node->attributes->getNamedItem('region')->value,
            'country' => $location_node->attributes->getNamedItem('country')->value
        );
        
        $units_node = $xml->getElementsByTagNameNS($yahoo_ns, 'units')->item(0);
        $units = array(
            'temperature' =>$units_node->attributes->getNamedItem('temperature')->value,
            'distance' =>$units_node->attributes->getNamedItem('distance')->value,
            'pressure' => $units_node->attributes->getNamedItem('pressure')->value,
            'speed' => $units_node->attributes->getNamedItem('speed')->value
        );
        
        $wind_node = $xml->getElementsByTagNameNS($yahoo_ns, 'wind')->item(0);
        $wind = array(
            'chill' => $wind_node->attributes->getNamedItem('chill')->value,
            'direction' => $this->wind_direction[round($wind_node->attributes->getNamedItem('direction')->value / 45)],     
            'speed' => $wind_node->attributes->getNamedItem('speed')->value
        );
        
        
        $atmosphere_node = $xml->getElementsByTagNameNS($yahoo_ns, 'atmosphere')->item(0);
        $atmosphere = array(
            'humidity' => $atmosphere_node->attributes->getNamedItem('humidity')->value,
            'visibility' => $atmosphere_node->attributes->getNamedItem('visibility')->value,
            'pressure' => $atmosphere_node->attributes->getNamedItem('pressure')->value,
            'rising' => $this->pressure_direction[$atmosphere_node->attributes->getNamedItem('rising')->value]
        );
        
        
        $astronomy_node = $xml->getElementsByTagNameNS($yahoo_ns, 'astronomy')->item(0);
        $astronomy = array(
            'sunrise' => $astronomy_node->attributes->getNamedItem('sunrise')->value,
            'sunset' => $astronomy_node->attributes->getNamedItem('sunset')->value
        );
        
        $condition_node = $xml->getElementsByTagNameNS($yahoo_ns, 'condition')->item(0);
        $condition = array(
            'text' => $condition_node->attributes->getNamedItem('text')->value,
            'code' => $condition_node->attributes->getNamedItem('code')->value,
            'image' => $condition_node->attributes->getNamedItem('code')->value,
            'temp' => $condition_node->attributes->getNamedItem('temp')->value,
            'date' => $condition_node->attributes->getNamedItem('date')->value
        );
        
        $lat = $xml->getElementsByTagNameNS($geo_ns, 'lat')->item(0)->nodeValue;
        $long = $xml->getElementsByTagNameNS($geo_ns, 'long')->item(0)->nodeValue;
        
        $forecasts = array();
        foreach ($xml->getElementsByTagNameNS($yahoo_ns, 'forecast') as $forecast)
        {
            array_push($forecasts, array(
                'day' => $forecast->attributes->getNamedItem('day')->value,
                'date' => $forecast->attributes->getNamedItem('date')->value,
                'low' => $forecast->attributes->getNamedItem('low')->value,
                'high' => $forecast->attributes->getNamedItem('high')->value,
                'text' => $forecast->attributes->getNamedItem('text')->value,
                'code' => $forecast->attributes->getNamedItem('code')->value
            ));
        }
        
        if ($this->metric)
        {
            $units['temperature'] = $this->metric_units[$units['temperature']];
            $units['distance'] = $this->metric_units[$units['distance']];
            $units['pressure'] = $this->metric_units[$units['pressure']];
            $units['speed'] = $this->metric_units[$units['speed']];
            
            $wind['chill'] = $this->convert($wind['chill'], TEMPERATURE);
            $wind['speed'] = $this->convert($wind['speed'], DISTANCE);
            
            $atmosphere['visibility'] = $this->convert($atmosphere['visibility'], DISTANCE);
            $atmosphere['pressure'] = $this->convert($atmosphere['pressure'], PRESSURE);
            
            $condition['temp'] = $this->convert($condition['temp'], TEMPERATURE);
            
            foreach ($forecasts as $key => $forecast)
            {
                $forecasts[$key]['low'] = $this->convert($forecast['low'], TEMPERATURE);
                $forecasts[$key]['high'] = $this->convert($forecast['high'], TEMPERATURE);
            }
        }
        
        return array(
            'language' => $language,
            'location' => $location,
            'units' => $units,
            'wind' => $wind,
            'atmosphere' => $atmosphere,
            'astronomy' => $astronomy,
            'condition' => $condition,
            'lat' => $lat,
            'long' => $long,
            'forecasts' => $forecasts
        );
    }
    
    /**
     * Weather::convert()
     * 
     * This method can be used for limited conversion between imperial and metric systems.
     * 
     * @param mixed $value Value to be converted.
     * @param mixed $type Type of conversion to take place (TEMPERATURE/PRESSURE/DISTANCE).
     * @param mixed $from System to convert from (METRIC/IMPERIAL).
     * @return string Converted (and formatted) value.
     */
    public function convert($value, $type = TEMPERATURE, $from = IMPERIAL)
    {        
        switch ($type)
        {
            case TEMPERATURE:
                if ($from == METRIC)
                {
                    return number_format($value * 1.8 + 32, 0);
                }
                return number_format(($value - 32) / 1.8, 0);
                
            case DISTANCE:
                if ($from == METRIC)
                {
                    return number_format($value * 0.621371192, 0);
                }
                return number_format($value * 1.609344, 0);
                
            case PRESSURE:
                if ($from == METRIC)
                {
                    return number_format($value * 0.0295301, 2);
                }
                return number_format($value * 33.8637526, 0);
        }
    }
    
    /**
     * Weather::get_url()
     * 
     * This method issues a request to a specified URL.
     * 
     * @param mixed $url URL to fetch.
     * @return string Response text.
     */
    protected function get_url($url)
    {
    	$response = file_get_contents($url);	
        return $response;
    }
}

/* end of file */

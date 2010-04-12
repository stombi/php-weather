Weather
=======

PHP 5 wrapper for Yahoo! weather. Caches with JSON or normal PHP serialization.

How to use
----------

    include('../lib/weather.php');
    
    $weather = new Weather();
    
    echo '<pre>';
    print_r($weather->get('SFXX0010'));
    echo '</pre>';
    
Natural output is metric. The following code enforces imperial:

    $weather = new Weather(false);
    
Cache is enabled by default, with an expiry of 60 minutes. You can alter this behaviour with the following code:

    $weather = new Weather(true, 0);
    
The complete parameter list of the Weather class constructor is:

    new Weather(bool $metric, [int $cache_minutes, [string $cache_dir, [int $cache_format]]);
    
Find your zip or area code on www.weather.com!
    
Output
------

    stdClass Object
    (
        [language] => en-us
        [location] => stdClass Object
            (
                [city] => Cape Town
                [region] => 
                [country] => SF
            )
    
        [units] => stdClass Object
            (
                [temperature] => C
                [distance] => km
                [pressure] => mb
                [speed] => kph
            )
    
        [wind] => stdClass Object
            (
                [chill] => 19
                [direction] => SW
                [speed] => 5
            )
    
        [atmosphere] => stdClass Object
            (
                [humidity] => 73
                [visibility] => 10
                [pressure] => 1,015
                [rising] => rising
            )
    
        [astronomy] => stdClass Object
            (
                [sunrise] => 7:05 am
                [sunset] => 6:26 pm
            )
    
        [condition] => stdClass Object
            (
                [text] => Fair
                [code] => 33
                [image] => 33
                [temp] => 19
                [date] => Mon, 12 Apr 2010 8:00 pm SAST
            )
    
        [lat] => -33.97
        [long] => 18.6
        [forecasts] => stdClass Object
            (
                [0] => stdClass Object
                    (
                        [day] => Mon
                        [date] => 12 Apr 2010
                        [low] => 16
                        [high] => 28
                        [text] => Clear
                        [code] => 31
                    )
    
                [1] => stdClass Object
                    (
                        [day] => Tue
                        [date] => 13 Apr 2010
                        [low] => 15
                        [high] => 27
                        [text] => Partly Cloudy
                        [code] => 30
                    )
    
            )
    
    )
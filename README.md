Weather
=======

PHP 5 wrapper for Yahoo! weather.

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

    new Weather(bool $metric, [int $cache, [string $cache_dir]]);
    
Output
------

    Array
    (
        [language] => en-us
        [location] => Array
            (
                [city] => Cape Town
                [region] => 
                [country] => SF
            )
    
        [units] => Array
            (
                [temperature] => C
                [distance] => km
                [pressure] => mb
                [speed] => kph
            )
    
        [wind] => Array
            (
                [chill] => 24
                [direction] => S
                [speed] => 13
            )
    
        [atmosphere] => Array
            (
                [humidity] => 44
                [visibility] => 10
                [pressure] => 1,014
                [rising] => steady
            )
    
        [astronomy] => Array
            (
                [sunrise] => 7:05 am
                [sunset] => 6:26 pm
            )
    
        [condition] => Array
            (
                [text] => Fair
                [code] => 34
                [image] => 34
                [temp] => 24
                [date] => Mon, 12 Apr 2010 6:00 pm SAST
            )
    
        [lat] => -33.97
        [long] => 18.6
        [forecasts] => Array
            (
                [0] => Array
                    (
                        [day] => Mon
                        [date] => 12 Apr 2010
                        [low] => 16
                        [high] => 28
                        [text] => Clear
                        [code] => 31
                    )
    
                [1] => Array
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
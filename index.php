<?php

// 2015 Zarino apologies in advance for 2010 Zarino's poor PHP programming. We all live and learn, right?

function markToGrade($mark){
    if($mark >= 70){
        $grade = '1st';
    } else if($mark >= 60){
        $grade = '2:1';
    } else if($mark >= 50){
        $grade = '2:2';
    } else if($mark >= 40){
        $grade = '3rd';
    } else {
        $grade = 'FAIL';
    }
    return $grade;
}

if(!empty($_POST)){

    //    for each year...
    foreach(array('first','second','third') as $prefix){
        //    ...put input values into an array...
        for ($i = 1; $i <= 8; $i++) {
            if((!empty($_POST[$prefix . 'yearmark' . $i])) && empty($errors)) {
                if(is_numeric($_POST[$prefix . 'yearmark' . $i]) && ($_POST[$prefix . 'yearmark' . $i] >= 0) && ($_POST[$prefix . 'yearmark' . $i] <= 100)){
                    ${$prefix . 'yearmarks'}[] = str_replace('%', '', htmlentities(trim($_POST[$prefix . 'yearmark' . $i]), ENT_QUOTES));
                } else {
                    $errors[] = 'Please ensure all supplied marks are numbers between 0 and 100.';
                }

            }
        }
        //    if no errors...
        if(!isset($errors)){
            //    if they supplied marks for this year...
            if(isset(${$prefix . 'yearmarks'})){
                //    ...if there are 8 marks for this year...
                if(sizeof(${$prefix . 'yearmarks'}) == '8') {
                    //    ...sort the marks, lowest to highest...
                    sort(${$prefix . 'yearmarks'});
                    //    ...decide how many marks to remove...
                    if($prefix == 'first'){ $remove = '2'; } else { $remove = '1'; }
                    //    ...remove that many of the lowest figures...
                    ${$prefix . 'yeartopmarks'} = array_slice(${$prefix . 'yearmarks'}, $remove);
                    //    ...calculate the sum of the top figures...
                    ${$prefix . 'yeartotal'} = array_sum(${$prefix . 'yeartopmarks'});
                    //    ...apply weighting...
                    if($prefix == 'first'){
                        ${$prefix . 'yearweighted'} = ${$prefix . 'yeartotal'};
                    } else if($prefix == 'second'){
                        ${$prefix . 'yearweighted'} = ${$prefix . 'yeartotal'} * 3;
                    } else if($prefix == 'third'){
                        ${$prefix . 'yearweighted'} = ${$prefix . 'yeartotal'} * 5;
                    }
                    $notes[] = 'Your ' . $prefix . ' year total was: ' . ${$prefix . 'yeartotal'} . ' (that&rsquo;s ' . ${$prefix . 'yearweighted'} . ' when weighted)';
                //    ...if there aren't 8 marks for this year...
                } else {
                    //    find out their average mark...
                    ${$prefix . 'yeartotal'} = array_sum(${$prefix . 'yearmarks'}) / sizeof(${$prefix . 'yearmarks'});
                    //    ...apply weighting...
                    if($prefix == 'first'){
                        ${$prefix . 'yearweighted'} = ${$prefix . 'yeartotal'} * 6;
                    } else if($prefix == 'second'){
                        ${$prefix . 'yearweighted'} = ${$prefix . 'yeartotal'} * 7 * 3;
                    } else if($prefix == 'third'){
                        ${$prefix . 'yearweighted'} = ${$prefix . 'yeartotal'} * 7 * 5;
                    }
                    $notes[] = 'You only supplied ' . sizeof(${$prefix . 'yearmarks'}) . ' ' . $prefix . ' year marks. We have extrapolated from the average of those ' . sizeof(${$prefix . 'yearmarks'}) . ' marks to give you an assumed ' . $prefix . ' year total of: ' . ${$prefix . 'yeartotal'} . ' (that&rsquo;s ' . ${$prefix . 'yearweighted'} . ' when weighted).';
                }
            //    ...if they didn't supply any marks for this year...
            } else {
                $notes[] = 'You did not supply any ' . $prefix . ' year marks.';
            }
        }

    }

    //    if they've supplied all three years' marks...
    if(isset($thirdyearweighted) && isset($secondyearweighted) && isset($firstyearweighted)){
        //    check whether they've supplied all 8 of the third year's marks...
        if(sizeof($thirdyearmarks) == 8) {
            //    add the weighted totals together and divide by 62, then round up/down...
            $finalmark = round(($thirdyearweighted + $secondyearweighted + $firstyearweighted) / 62);
            $finalgrade = markToGrade($finalmark);
            $results[] = 'Your final grade is a <strong>' . $finalgrade . '</strong> (with a mark of ' . $finalmark . '&#37)';
        } else {
            //    they only supplied *some* of the third year marks...
            $finalmark = round(($secondyearweighted + $firstyearweighted) / 27);
            $finalgrade = markToGrade($finalmark);
            $results[] = 'So far, not counting your third year, you&rsquo;re heading for a <strong>' . $finalgrade . '</strong> (with a mark of ' . $finalmark . '&#37).';

            //    counting any third-year marks they supplied, what do they need to get on average in the remaining courses to maintain or improve that grade?
            if($finalgrade == '1st'){
                $thisgrade = '70';
            } else if($finalgrade == '2:1'){
                $thisgrade = '60';
                $nextgrade = '70';
            } else if($finalgrade == '2:2'){
                $thisgrade = '50';
                $nextgrade = '60';
            } else if($finalgrade == '3rd'){
                $thisgrade = '40';
                $nextgrade = '50';
            } else if($finalgrade == 'FAIL'){
                $nextgrade = '40';
            }
            if(isset($thisgrade)){
                $nextyearavg['maintain'] = (($thisgrade*62) - (6*(array_sum($firstyearmarks)/8)) - (3*(7*(array_sum($firstyearmarks)/8)))) / (5*7);
                $nextyearavg['maintain'] = (($nextyearavg['maintain'] * 8) - (array_sum($thirdyearmarks))) / (8 - sizeof($thirdyearmarks));
                $results[] = 'Bearing in mind the mark(s) you&rsquo;ve already achieved in your third year, to graduate with a <strong>' . $finalgrade . '</strong>, you need to aim for a minimum average mark of ' . ceil($nextyearavg['maintain']) . ' in each of your remaining third-year courses.';
            }
            if(isset($nextgrade)){
                $nextyearavg['improve'] = (($nextgrade*62) - (6*(array_sum($firstyearmarks)/8)) - (3*(7*(array_sum($firstyearmarks)/8)))) / (5*7);
                $nextyearavg['improve'] = (($nextyearavg['improve'] * 8) - (array_sum($thirdyearmarks))) / (8 - sizeof($thirdyearmarks));
                $results[] = 'Bearing in mind the mark(s) you&rsquo;ve already achieved in your third year, to graduate with a <strong>' . markToGrade($nextgrade) . '</strong>, you need to aim for a minimum average mark of ' . ceil($nextyearavg['improve']) . ' in each of your remaining third-year courses.';
            }

        }
        if(sizeof($firstyearmarks) < 8) { $results[] = ' Note: since you didn&rsquo;t supply <em>all</em> of your first year&rsquo;s figures, this result assumes your first year average is equal to the average of all the first-year marks you <em>did</em> supply.';
        }
        if(sizeof($secondyearmarks) < 8) { $results[] = ' Note: since you didn&rsquo;t supply <em>all</em> of your second year&rsquo;s figures, this result assumes your second year average is equal to the average of all the second-year marks you <em>did</em> supply.';
        }
    //    if they only supplied first two years' marks...
    } else if(isset($secondyearweighted) && isset($firstyearweighted)){
        //    add the weighted totals together and divide by 27, then round up/down...
        $finalmark = round(($secondyearweighted + $firstyearweighted) / 27);
        $finalgrade = markToGrade($finalmark);
        $results[] = 'So far, after your first two years, you&rsquo;re heading for a <strong>' . $finalgrade . '</strong> (with a mark of ' . $finalmark . '&#37).';
        //    find out what average they need next year to maintain, or better, this grade...
        if($finalgrade == '1st'){
            $thisgrade = '70';
        } else if($finalgrade == '2:1'){
            $thisgrade = '60';
            $nextgrade = '70';
        } else if($finalgrade == '2:2'){
            $thisgrade = '50';
            $nextgrade = '60';
        } else if($finalgrade == '3rd'){
            $thisgrade = '40';
            $nextgrade = '50';
        } else if($finalgrade == 'FAIL'){
            $nextgrade = '40';
        }
        if(isset($thisgrade)){
            $nextyearavg['maintain'] = (($thisgrade*62) - (6*(array_sum($firstyearmarks)/8)) - (3*(7*(array_sum($firstyearmarks)/8)))) / (5*7);
            $results[] = 'To graduate with that <strong>' . $finalgrade . '</strong>, you need to aim for a minimum average mark of ' . ceil($nextyearavg['maintain']) . ' in each of your third-year courses.';
        }
        if(isset($nextgrade)){
            $nextyearavg['improve'] = (($nextgrade*62) - (6*(array_sum($firstyearmarks)/8)) - (3*(7*(array_sum($firstyearmarks)/8)))) / (5*7);
            $results[] = 'To graduate with a <strong>' . markToGrade($nextgrade) . '</strong>, you need to aim for a minimum average mark of ' . ceil($nextyearavg['improve']) . ' in each of your third-year courses.';
        }
        if(sizeof($firstyearmarks) < 8) { $results[] = ' Note: since you didn&rsquo;t supply <em>all</em> of your first year&rsquo;s figures, this result assumes your first year average is equal to the average of all the first-year marks you <em>did</em> supply.';
        }
        if(sizeof($secondyearmarks) < 8) { $results[] = ' Note: since you didn&rsquo;t supply <em>all</em> of your second year&rsquo;s figures, this result assumes your second year average is equal to the average of all the second-year marks you <em>did</em> supply.';
        }
    } else if(isset($firstyearweighted)){
        //    divide by 6, then round up/down...
        $finalmark = round($firstyearweighted / 6);
        $finalgrade = markToGrade($finalmark);
        $results[] = 'So far, after your first year, you&rsquo;re heading for a <strong>' . $finalgrade . '</strong> (with a mark of ' . $finalmark . '&#37)';
    }

}

?><!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="UTF-8" />
        <title>gold.zarino.co.uk &raquo; Calculate your yearly grade at Goldsmiths</title>
        <meta name="description" content="Work out what grade you&rsquo;re heading for so far in your degree at Goldsmiths, using this free Grade Calculator, designed and developed by Zarino Zappia">
        <link href="css/screen.css" type="text/css" media="all" rel="stylesheet">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
        <script src="js/custom.js" type="text/javascript"></script>
        <script type="text/javascript">
          var _gaq = _gaq || [];
          _gaq.push(['_setAccount', 'UA-18117917-5']);
          _gaq.push(['_trackPageview']);

          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();
        </script>
    </head>
    <body>

    <div id="header">
        <h1>Goldsmiths Grade Calculator</h1>
        <p>Designed and developed by <a href="http://zarino.co.uk" title="Zarino makes stuff like this for fun">Zarino Zappia</a></p>
    </div>

    <?php

        if(!empty($errors)){
            echo '<ul id="errors">';
            echo '<li class="title"><h3>Errors:</h3></li>';
            foreach($errors as $error){
                echo '<li>' . $error . '</li>';
            }
            echo '</ul>';
        } else if(!empty($results)){
            echo '<ul id="results">';
            echo '<li class="title"><h2>Results:</h2></li>';
            foreach($results as $result){
                echo '<li>' . $result . '</li>';
            }
            echo '</ul>';
        }

        if(!empty($notes)){
            echo '<ul id="notes">';
            echo '<li class="title"><h3>Notes:</h3></li>';
            foreach($notes as $note){
                echo '<li>' . $note . '</li>';
            }
            echo '</ul>';
        }

        if(empty($errors) && empty($notes) && empty($results)){
            echo '<p id="intro">Are you a first, second or third-year student at Goldsmiths, wondering what grade you&rsquo;re heading for so far? Or maybe you have just received all of your marks for the year and you&rsquo;d like to see what grade you got. Well, this calculator&rsquo;s just what you need.</p>
            <p id="intro2">Each year of your degree is worth <strong>4</strong> units. This is often made up of 8 half-unit marks. Those marks are sent to you, at the end of each year, on a form entitled &lsquo;Notification of Results&rsquo;. Or, you can usually tot up a mark by looking at the scores you got for the essay(s) on that particular course. Be careful though, some courses (like practical courses for Media students) are doubly &lsquo;weighted&rsquo;, counting for a full unit, rather than just half &mdash; see the note below for more info.</p>';
        }

    ?>

        <form action="/" method="post">
            <div class="year" id="firstyear">
                <dl>
                    <dt><h3>1st Year marks</h3></dt><?php
                    $preface = 'firstyearmark';
                    for ($i = 1; $i <= 8; $i++) {
                        echo '
                    <dd><input ';
                        if(!empty($_POST[$preface . $i]) && !is_numeric($_POST[$preface . $i])) { echo 'class="error"'; }
                        echo ' type="text" id="' . $preface . $i . '" name="' . $preface . $i . '" value="';
                        if(!empty($_POST[$preface . $i])) { echo htmlentities($_POST[$preface . $i], ENT_QUOTES); }
                        echo '" /></dd>';
                    }
                    ?>
                </dl>
            </div>
            <div class="year" id="secondyear">
                <dl>
                    <dt><h3>2nd Year marks</h3></dt><?php
                    $preface = 'secondyearmark';
                    for ($i = 1; $i <= 8; $i++) {
                        echo '
                    <dd><input type="text" id="' . $preface . $i . '" name="' . $preface . $i . '" value="';
                        if(isset($_POST[$preface . $i])) { echo htmlentities($_POST[$preface . $i], ENT_QUOTES); }
                        echo '" /></dd>';
                    }
                    ?>
                </dl>
            </div>
            <div class="year" id="thirdyear">
                <dl>
                    <dt><h3>3rd Year marks</h3></dt><?php
                    $preface = 'thirdyearmark';
                    for ($i = 1; $i <= 8; $i++) {
                        echo '
                    <dd><input type="text" id="' . $preface . $i . '" name="' . $preface . $i . '" value="';
                        if(isset($_POST[$preface . $i])) { echo htmlentities($_POST[$preface . $i], ENT_QUOTES); }
                        echo '" /></dd>';
                    }
                    ?>
                </dl>
            </div>
            <div id="remember">
                <h3>Remember</h3>
                <ul>
                    <li>Some courses (eg: practical courses for Media &amp; Comms students) count for <strong>TWO</strong> marks. This is denoted by a &lsquo;Credit&rsquo; of &lsquo;30&rsquo; (rather than &lsquo;15&rsquo;) on your <em>Notification of Results</em> certificate. If you have one of these double-strength marks, you must enter the mark twice, above.</li>
                    <li>If you don&rsquo;t know all of your marks for a certain year, just fill in the ones you <em>do</em> know, and the calculator will extrapolate out an average using those. The final result will only be an estimate, however.</li>
                </ul>
            </div>
            <input type="submit" name="submit" id="submit" value="Calculate my marks" />
        </form>
        <ul id="disclaimers">
            <li class="title"><h4>Disclaimers:</h4></li>
            <li>This calculator uses the marking scheme laid out in <a href="http://www.gold.ac.uk/registry/courseunit_calculation/">this document from the Goldsmiths Registry Office</a></li>
            <li>This calculator assumes that the following grade boundaries are used by Goldsmiths markers: 70% for a 1st, 60% for a 2:1, 50% for a 2:2, 40% for a 3rd. In actuality, the grade boundaries may differ slightly.</li>
            <li>This calculator is provided <em>as is</em>, with no guarantee of absolute accuracy or freedom from coding or mathematical errors. Do not base your whole future on what this calculator says. Zarino accepts no liability for any loss of marks you may incur by sitting back on your laurels because you <em>thought</em> you were safe with a 2:1.</li>
            <li><a href="/LICENSE.txt">AGPL open source licensed</a>. Source code on <a href="https://github.com/zarino/goldsmiths-grade-calculator">GitHub</a></li>
        </ul>
    </body>
</html>

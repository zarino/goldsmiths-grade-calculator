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

function arrayToOxfordCommaList($array) {
    $last_element = array_pop($array);
    array_push($array, 'and ' . $last_element);
    return implode(', ', $array);
}

function marksToConsiderForYear($year) {
    if($year == 'first'){
        return 6;
    } else {
        return 7;
    }
}

function weightingForYear($year) {
    if($year == 'first'){
        return 1;
    } else if($year == 'second') {
        return 3;
    } else {
        return 5;
    }
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
                    //    ...sort the marks, highest to lowest...
                    rsort(${$prefix . 'yearmarks'});
                    //    ...keep only the top marks we want to consider...
                    ${$prefix . 'yeartopmarks'} = array_slice(${$prefix . 'yearmarks'}, 0, marksToConsiderForYear($prefix));
                    //    ...calculate the sum of the top figures...
                    ${$prefix . 'yearsubtotal'} = array_sum(${$prefix . 'yeartopmarks'});
                    //    ...apply weighting...
                    ${$prefix . 'yearweighted'} = ${$prefix . 'yearsubtotal'} * weightingForYear($prefix);

                    $notes[] = sprintf(
                        'Your top %s marks for the %s year were %s, making a subtotal of %s. The %s year subtotal is multiplied by %s, making for a <strong>%s year total of %s</strong>.',
                        marksToConsiderForYear($prefix),
                        $prefix,
                        arrayToOxfordCommaList(${$prefix . 'yeartopmarks'}),
                        ${$prefix . 'yearsubtotal'},
                        $prefix,
                        weightingForYear($prefix),
                        $prefix,
                        ${$prefix . 'yearweighted'}
                    );

                //    ...if there aren't 8 marks for this year...
                } else {
                    //    find out their average mark...
                    ${$prefix . 'yearaverage'} = array_sum(${$prefix . 'yearmarks'}) / sizeof(${$prefix . 'yearmarks'});
                    //    ...apply weighting...
                    ${$prefix . 'yearsubtotal'} = ${$prefix . 'yearaverage'} * marksToConsiderForYear($prefix);
                    ${$prefix . 'yearweighted'} = ${$prefix . 'yearsubtotal'} * weightingForYear($prefix);

                    $notes[] = sprintf(
                        'You only supplied %s marks for your %s year, so we have extrapolated from the average of those marks (%.2f) to give a subtotal of %s. The %s year subtotal is multiplied by %s, making for an <strong>estimated %s year total of %s</strong>.',
                        sizeof(${$prefix . 'yearmarks'}),
                        $prefix,
                        ${$prefix . 'yearaverage'},
                        ${$prefix . 'yearsubtotal'},
                        $prefix,
                        weightingForYear($prefix),
                        $prefix,
                        ${$prefix . 'yearweighted'}
                    );
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
            $finaldivisor = marksToConsiderForYear('first') * weightingForYear('first') + marksToConsiderForYear('second') *  weightingForYear('second') + marksToConsiderForYear('third') * weightingForYear('third'); // 62
            $finalmark = round(($thirdyearweighted + $secondyearweighted + $firstyearweighted) / $finaldivisor);
            $finalgrade = markToGrade($finalmark);
            $results[] = 'Your final grade is a <strong>' . $finalgrade . '</strong> (with a final weighted average mark of ' . $finalmark . '&#37)';
            $notes[] = sprintf(
                'Since you’ve provided marks for all three years of your course, we find the total (%s + %s + %s = %s), then divide it by %s (ie: %s&times;%s + %s&times;%s + %s&times;%s) to give a <strong>weighted average of %s</strong>.',
                $firstyearweighted,
                $secondyearweighted,
                $thirdyearweighted,
                $firstyearweighted + $secondyearweighted + $thirdyearweighted,
                $finaldivisor,
                marksToConsiderForYear('first'),
                weightingForYear('first'),
                marksToConsiderForYear('second'),
                weightingForYear('second'),
                marksToConsiderForYear('third'),
                weightingForYear('third'),
                $finalmark
            );
        } else {
            //    they only supplied *some* of the third year marks...
            $finaldivisor = marksToConsiderForYear('first') * weightingForYear('first') + marksToConsiderForYear('second') *  weightingForYear('second'); // 27
            $finalmark = round(($secondyearweighted + $firstyearweighted) / $finaldivisor);
            $finalgrade = markToGrade($finalmark);
            $results[] = 'So far, not counting your third year, you&rsquo;re heading for a <strong>' . $finalgrade . '</strong> (with a final weighted average mark of ' . $finalmark . '&#37).';
            $notes[] = sprintf(
                'Ignoring your incomplete third year, your took your first and second year total (%s + %s = %s) and divided it by %s (ie: %s&times;%s + %s&times;%s) to give a <strong>weighted average of %s</strong>.',
                $firstyearweighted,
                $secondyearweighted,
                $firstyearweighted + $secondyearweighted,
                $finaldivisor,
                marksToConsiderForYear('first'),
                weightingForYear('first'),
                marksToConsiderForYear('second'),
                weightingForYear('second'),
                $finalmark
            );

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
                $results[] = 'Bearing in mind the marks you’ve already achieved in your third year, to graduate with a <strong>' . $finalgrade . '</strong>, you need to achieve a minimum average mark of ' . ceil($nextyearavg['maintain']) . ' in each of your remaining third-year courses.';
            }
            if(isset($nextgrade)){
                $nextyearavg['improve'] = (($nextgrade*62) - (6*(array_sum($firstyearmarks)/8)) - (3*(7*(array_sum($firstyearmarks)/8)))) / (5*7);
                $nextyearavg['improve'] = (($nextyearavg['improve'] * 8) - (array_sum($thirdyearmarks))) / (8 - sizeof($thirdyearmarks));
                $results[] = 'Bearing in mind the marks you’ve already achieved in your third year, to graduate with a <strong>' . markToGrade($nextgrade) . '</strong>, you need to achieve a minimum average mark of ' . ceil($nextyearavg['improve']) . ' in each of your remaining third-year courses.';
            }

        }
        if(sizeof($firstyearmarks) < 8) { $results[] = ' Note: since you didn&rsquo;t supply <em>all</em> of your first year&rsquo;s figures, this result assumes your first year average is equal to the average of all the first-year marks you <em>did</em> supply.';
        }
        if(sizeof($secondyearmarks) < 8) { $results[] = ' Note: since you didn&rsquo;t supply <em>all</em> of your second year&rsquo;s figures, this result assumes your second year average is equal to the average of all the second-year marks you <em>did</em> supply.';
        }
    //    if they only supplied first two years' marks...
    } else if(isset($secondyearweighted) && isset($firstyearweighted)){
        //    add the weighted totals together and divide by 27, then round up/down...
        $finaldivisor = marksToConsiderForYear('first') * weightingForYear('first') + marksToConsiderForYear('second') *  weightingForYear('second'); // 27
        $finalmark = round(($secondyearweighted + $firstyearweighted) / $finaldivisor);
        $finalgrade = markToGrade($finalmark);
        $results[] = 'So far, after your first two years, you&rsquo;re heading for a <strong>' . $finalgrade . '</strong> (with a final weighted average mark of ' . $finalmark . '&#37).';
        $notes[] = sprintf(
            'The estimated final grade above is based on a your first and second year total (%s + %s = %s), divided by %s (ie: %s&times;%s + %s&times;%s), to give a <strong>weighted average of %s</strong>.',
            $firstyearweighted,
            $secondyearweighted,
            $firstyearweighted + $secondyearweighted,
            $finaldivisor,
            marksToConsiderForYear('first'),
            weightingForYear('first'),
            marksToConsiderForYear('second'),
            weightingForYear('second'),
            $finalmark
        );

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
            $results[] = 'To graduate with that <strong>' . $finalgrade . '</strong>, you need to achieve a minimum average mark of ' . ceil($nextyearavg['maintain']) . ' in each of your third-year courses.';
        }
        if(isset($nextgrade)){
            $nextyearavg['improve'] = (($nextgrade*62) - (6*(array_sum($firstyearmarks)/8)) - (3*(7*(array_sum($firstyearmarks)/8)))) / (5*7);
            $results[] = 'To graduate with a <strong>' . markToGrade($nextgrade) . '</strong>, you need to achieve a minimum average mark of ' . ceil($nextyearavg['improve']) . ' in each of your third-year courses.';
        }
        if(sizeof($firstyearmarks) < 8) { $results[] = ' Note: since you didn&rsquo;t supply <em>all</em> of your first year&rsquo;s figures, this result assumes your first year average is equal to the average of all the first-year marks you <em>did</em> supply.';
        }
        if(sizeof($secondyearmarks) < 8) { $results[] = ' Note: since you didn&rsquo;t supply <em>all</em> of your second year&rsquo;s figures, this result assumes your second year average is equal to the average of all the second-year marks you <em>did</em> supply.';
        }
    } else if(isset($firstyearweighted)){
        //    divide by 6, then round up/down...
        $finaldivisor = marksToConsiderForYear('first') * weightingForYear('first'); // 6
        $finalmark = round($firstyearweighted / $finaldivisor);
        $finalgrade = markToGrade($finalmark);
        $results[] = 'So far, after your first year, you&rsquo;re heading for a <strong>' . $finalgrade . '</strong> (with a final weighted average mark of ' . $finalmark . '&#37)';
        $notes[] = sprintf(
            'The estimated final grade above is based on a your first year total (%s), divided by %s (ie: %s&times;%s), to give a <strong>weighted average of %s</strong>.',
            $firstyearweighted,
            $finaldivisor,
            marksToConsiderForYear('first'),
            weightingForYear('first'),
            $finalmark
        );
    }

}

?><!doctype html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
    <meta name="HandHeldFriendly" content="true">
    <meta name="mobileoptimized" content="0">
    <title>gold.zarino.co.uk &raquo; Calculate your yearly grade at Goldsmiths</title>
    <meta name="description" content="Work out what grade you&rsquo;re heading for so far in your degree at Goldsmiths, using this free, totally unofficial Grade Calculator, based on the college&rsquo;s published mark scheme.">
    <link href="css/screen.css" rel="stylesheet">
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

    <header class="site-header">
        <div class="container">
            <h1>
                <small><a href="https://zarino.co.uk">Zarino</a>’s totally unofficial</small>
                Goldsmiths Grade Calculator
            </h1>
            <p class="byline">Based on the college’s <a href="https://www.gold.ac.uk/students/studying/assessments/undergraduate-final-result-calculation/">official undergraduate final result calculations</a>. Not run or maintained by Goldsmiths.</p>
        </div>
    </header>

    <div class="container">

      <?php if ( !empty($errors) ) { ?>
        <div class="feedback feedback--error">
            <h2>Errors</h3>
            <ul>
              <?php foreach ( $errors as $error ) { ?>
                <li><?php echo $error; ?></li>
              <?php } ?>
            </ul>
        </div>
      <?php } else if ( !empty($results) ) { ?>
        <div class="feedback feedback--results">
            <h2>Results</h2>
            <ul>
              <?php foreach ( $results as $result ) { ?>
                <li><?php echo $result; ?></li>
              <?php } ?>
            </ul>
        </div>
      <?php } ?>

      <?php if ( !empty($notes) ) { ?>
        <div class="feedback feedback--notes">
            <h2>Notes</h2>
            <ul>
              <?php foreach ( $notes as $note ) { ?>
                <li><?php echo $note; ?></li>
              <?php } ?>
            </ul>
        </div>
      <?php } ?>

        <form action="/" method="post">

            <h2>Enter up to 8 credit marks for each year you’ve studied</h2>

            <details class="how-it-works">
                <summary>Need help?</summary>
                <ul>
                    <li>A “credit mark” is the mark you were awarded for completing a module (eg: the mark you were given for your module’s final essay, usually a number around 50–70).</li>
                    <li>Each year of your course counts for 120 “credits”. This might be in the form of eight 15-credit modules. Or it might include some 30-credit modules (eg: practical modules for Media &amp; Comms students).</li>
                    <li>If your course includes one of these double-strength modules, you should enter the credit mark for that module twice in the boxes below.</li>
                    <li>Your final grade will be calculated from the 90 best credit marks from your first year, the 105 best credit marks from your second year, and the 105 best credit marks from your third year. <a href="https://www.gold.ac.uk/students/studying/assessments/undergraduate-final-result-calculation/">For more information see the official guidance.</a></li>
                    <li>If you don&rsquo;t know all of your marks for a certain year, just fill in the ones you <em>do</em> know, and the calculator will extrapolate an average to give you an <em>estimated</em> final result.</li>
                </ul>
            </details>

          <?php
              $years = array(
                  'firstyear' => 'first year',
                  'secondyear' => 'second year',
                  'thirdyear' => 'third year'
              );
              foreach ( $years as $slug => $title ):
          ?>

            <fieldset>
                <legend><?php echo ucfirst($title); ?> marks</legend>
                <ul>
                    <?php
                    $preface = $slug . 'mark';
                    for ($i = 1; $i <= 8; $i++) { ?>
                        <li <?php if(!empty($_POST[$preface . $i]) && !is_numeric($_POST[$preface . $i])) { echo 'class="error"'; } ?> >
                            <label for="<?php echo $preface . $i; ?>">
                                <?php echo ucfirst($title); ?>,
                                credit mark <?php echo $i; ?>
                            </label>
                            <input type="text" id="<?php echo $preface . $i; ?>" name="<?php echo $preface . $i; ?>" pattern="[0-9]{0,3}" title="A credit mark, from 0–100" value="<?php if(!empty($_POST[$preface . $i])) { echo htmlentities($_POST[$preface . $i], ENT_QUOTES); } ?>">
                        </li>
                    <?php } ?>
                </ul>
            </fieldset>

          <?php
              endforeach;
          ?>

            <button type="submit" name="submit">Calculate my grade</button>

        </form>

    </div>

    <footer>
        <div class="container">
            <ul>
                <li>For more information than you’d ever want about assessments at Goldsmiths, head on over to <a href="https://www.gold.ac.uk/students/studying/assessments/" style="word-wrap: break-word">gold.ac.uk/students/studying/assessments</a>.</li>
                <li>This calculator assumes grade boundaries of: 70% for a 1st, 60% for a 2:1, 50% for a 2:2, 40% for a 3rd. In actuality, the grade boundaries may differ slightly.</li>
                <li>You can inspect the source code for this calculator at <a href="https://github.com/zarino/goldsmiths-grade-calculator">GitHub</a>.</li>
                <li>Distributed under an <a href="/LICENSE.txt">AGPL open source license</a>.</li>
            </ul>
        </div>
    </footer>

</body>
</html>

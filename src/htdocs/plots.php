<?php

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Plots';
  $NAVIGATION = true;
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

?>

<p>These plots depict north, east and up components of the station as a function of time. The station velocities are in a North America fixed reference frame derived from <a href="http://itrf.ensg.ign.fr/general.php">ITRF2005</a> using an Euler pole defined by Altamimi et al.</p>

<ul id="notes">
  <li>Error bars are plus/minus one standard deviation</li>
  <li>Error bars on individual observations = formal-error x scale-factor</li>
  <li>Currently the scale-factors are: N:3.0, E:4.0, U:3.0</li>
  <li>Red points are positions determined using final orbits</li>
  <li>Blue points are positions determined using preliminary orbits</li>
</ul>

<p>Vel-stddev = sqrt(WN**2+RW**2) where WN is a white noise contribution from propagating the error bars on the observations and RW is a random walk contribution (= a*sqrt(timespan) / timespan where a is 0.001 m/yr**1/2)</p>

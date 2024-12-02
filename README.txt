HOMEWORK PLUGIN
===============
This plugin adds a new activity ressource as an option to add to a course,
as well as displaying such homeworks on the dashboard.

The activity module adds a new activity option when adding activities to a
course under the course page. This activity option is homework, which the user
can choose to edit to include a description, start time, due date, and material
for the homework.

Material can be created while creating homework, such that it is added to the
database, and can be reused in homework later, e.g. a course book that is reused across
multiple homeworks. The types of material that can be created are litterature,
links, and videos. Videos are embedded in Moodle, and can be viewed directly on the page.

Homework that has been added as an activity to the course is displayed on the
dashboard, as well as in the calendar. Displaying the calendar
is handled by the block module.

Homework can be filtered on the dashboard based on courses, e.g. the user only
wishes to see homework from the 'Database Systems' course, and filtered to shown future,
past or all homework. Additionally, displayed homework can be sorted based on certain
parameters, such as due date. Students can also download all materials assigned to a
homework activity as a single ZIP file.

The activity module can be found in:
server/moodle/mod/homework

The block module can be found in:
server/moodle/blocks/homework




MOODLE FOR WINDOWS 
==================

This package contains everything you need to run Moodle on a windows machine.

Moodle has been packaged with Apache, MariaDB and PHP.




HOW TO USE IT
=============

1. Run 'Start Moodle.exe' to start up the system.

2. Visit http://localhost/ to use your Moodle site!

3. Other people have to access it via http://xxx.xxx.xxx.xxx where
   xx.xx.xx.xx is the IP number or name of your Windows computer.

4. If you want to shut down the Moodle server, use 'Stop Moodle.exe'




TECHNICAL INFORMATION
=====================

1. Start/Stop Moodle.exe

'Start Moodle.exe' runs the xampp installation script
located in server/install/install.php. Then it starts Apache and MariaDB.
'Stop Moodle.exe' stops the Apache and MariaDB processes.


2. XAMPP (https://www.apachefriends.org/en/xampp.html)

You can use xampp executable files directly if you like.  They are 
located in the "server" directory. 
 

3. Moodle (https://moodle.org/)

All moodle files are located in server/moodle/


4. Performance Settings (https://docs.moodle.org/en/Performance)

In order to optimize your Moodle Environment, see the Moodle Performance docs. 




TROUBLESHOOTING
===============

If 'Start Moodle.exe' fails to work and the windows closes automatically, 
you may have something blocking port 80 on your machine.  Make sure there
are no other web servers running on this port, Skype is configured not 
to use port 80, firewalls are open etc

Another reason could be because PHP in this package needs the Microsoft
Visual C++ 2019 (VS16) or newer Redistributable package from:
https://learn.microsoft.com/en-us/cpp/windows/latest-supported-vc-redist

For more information, visit:
https://docs.moodle.org/en/Complete_install_packages_for_Windows

Do not rename the 'server/' folder. Otherwise 'Start Moodle.exe' and 
'Stop Moodle.exe' will stop working and you'll have to use xampp 
executable files. (xammp-control.exe)



Thank you for using Moodle!

Moodle HQ (https://moodle.com)


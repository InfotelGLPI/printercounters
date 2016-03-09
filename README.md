# printercounters
Plugin Printercounters pour GLPI

Mise en place d'un plugin de gestion des compteurs des imprimantes bas� sur le protocole SNMP.
Le plugin se base sur l'inventaire d�ja existant de GLPI, pour r�cup�rer les adresses IP des imprimantes de chaque entit�.
Le plugin permet :

* d'effectuer le relev� des compteurs des imprimantes sur le r�seau
* de configurer et de g�rer l'interrogation des compteurs pour chaque imprimante
* d'afficher et de g�rer les co�ts par entit�s

Contraintes : 

* Utilisable � partir de la version 5.4 de PHP.
* Le plugin doit pouvoir interroger 3000 � 4500 imprimantes potentielles.

Sp�cifications : https://forge.glpi-project.org/projects/glpi/wiki/SNMP_printer_counters
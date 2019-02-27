# printercounters
Plugin Printercounters pour GLPI

Ce plugin est sur Transifex - Aidez-nous à le traduire : https://www.transifex.com/infotelGLPI/GLPI_printercounters/

This plugin is on Transifex - Help us to translate : https://www.transifex.com/infotelGLPI/GLPI_printercounters/

Mise en place d'un plugin de gestion des compteurs des imprimantes basé sur le protocole SNMP.
Le plugin se base sur l'inventaire déja existant de GLPI, pour récupérer les adresses IP des imprimantes de chaque entité.
Le plugin permet :

* d'effectuer le relevé des compteurs des imprimantes sur le réseau
* de configurer et de gérer l'interrogation des compteurs pour chaque imprimante
* d'afficher et de gérer les coûts par entités

Contraintes : 

* Utilisable à partir de la version 5.4 de PHP.
* Le plugin doit pouvoir interroger 3000 à 4500 imprimantes potentielles.

Spécifications : https://forge.glpi-project.org/projects/glpi/wiki/SNMP_printer_counters

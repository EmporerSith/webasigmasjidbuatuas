<div class="content mt-5 pt-2">
    <div id="dynamic-map-container" style="width: 100%; height: calc(100vh - 120px);">
        <div id="map" style="width: 100%; height: 100%; color:black;"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var objk = new L.LayerGroup();
        var polygor = new L.LayerGroup();

        var map = L.map('map', {
            center: [-6.9639, 107.5200],
            zoom: 5,
            zoomControl: false,
            layers: []
        });

        var southWest = L.latLng(-85, -180); 
        var northEast = L.latLng(85, 180); 
        var bounds = L.latLngBounds(southWest, northEast);
        map.setMaxBounds(bounds);

        map.on('drag', function() {
            map.panInsideBounds(bounds, { animate: true });
        });

        var GoogleSatelliteHybrid = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
            maxZoom: 22,
            attribution: 'Latihan Web GIS'
        }).addTo(map);

        var Stadia_Outdoors = L.tileLayer('https://tiles.stadiamaps.com/tiles/outdoors/{z}/{x}/{y}{r}.{ext}', {
            minZoom: 0,
            maxZoom: 20,
            attribution: '&copy; Stadia Maps &copy; OpenMapTiles &copy; OpenStreetMap contributors',
            ext: 'png'
        });

        var Stadia_AlidadeSatellite = L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_satellite/{z}/{x}/{y}{r}.{ext}', {
            minZoom: 0,
            maxZoom: 20,
            attribution: '&copy; Stadia Maps &copy; OpenMapTiles &copy; OpenStreetMap contributors',
            ext: 'jpg'
        });

        var GoogleMaps = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { 
            opacity: 1.0, attribution: 'Latihan Web GIS' 
        });

        var GoogleRoads = L.tileLayer('https://mt1.google.com/vt/lyrs=h&x={x}&y={y}&z={z}', {
            opacity: 1.0, attribution: 'Latihan Web GIS' 
        });

        var baseLayers = {
            'Google Maps': GoogleMaps,
            'Google Roads': GoogleRoads,
            'Google Satellite Map': GoogleSatelliteHybrid,
            'Stadia Alidade Satellite Map': Stadia_AlidadeSatellite,
            'Stadia Outdoors Map': Stadia_Outdoors
        };

        var groupedOverlays = {
            "Dasar": {
                'Kota Bogor': polygor
            },
            "Khusus": {
                'Sarana Ibadah Kota Bogor': objk
            }
        };

        var osmUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
        var osmAttrib = 'Map data &copy; OpenStreetMap contributors';
        var osm2 = new L.TileLayer(osmUrl, {
            minZoom: 0,
            maxZoom: 13,
            attribution: osmAttrib
        });

        var rect1 = { color: "#ff1100", weight: 3 };
        var rect2 = { color: "#0000AA", weight: 1, opacity: 0, fillOpacity: 0 };

        var miniMap = new L.Control.MiniMap(osm2, {
            toggleDisplay: true,
            position: "bottomright",
            aimingRectOptions: rect1,
            shadowRectOptions: rect2
        }).addTo(map);

        L.Control.geocoder({
            position: "topleft",
            collapsed: true
        }).addTo(map);

        var locateControl = L.control.locate({
            position: "topleft",
            drawCircle: true,
            follow: true,
            setView: true,
            keepCurrentZoomLevel: true,
            markerStyle: { weight: 1, opacity: 0.8, fillOpacity: 0.8 },
            circleStyle: { weight: 1, clickable: false },
            icon: "fa fa-location-arrow",
            metric: false,
            strings: {
                title: "My location",
                popup: "You are within {distance} {unit} from this point",
                outsideMapBoundsMsg: "You seem located outside the boundaries of the map"
            },
            locateOptions: {
                maxZoom: 18,
                watch: true,
                enableHighAccuracy: true,
                maximumAge: 10000,
                timeout: 10000
            }
        }).addTo(map);

        var zoom_bar = new L.Control.ZoomBar({ position: 'topleft' }).addTo(map);

        L.control.coordinates({
            position: "bottomleft",
            decimals: 2,
            decimalSeperator: ",",
            labelTemplateLat: "Latitude: {y}",
            labelTemplateLng: "Longitude: {x}"
        }).addTo(map);

        L.control.scale({ metric: true, position: "bottomleft" }).addTo(map);

        var north = L.control({ position: "bottomleft" });
        north.onAdd = function(map) {
            var div = L.DomUtil.create("div", "info legend");
            div.innerHTML = '<img src="assets/arah-mata-angin.png" style="width:200px;">';
            return div;
        };
        north.addTo(map);

        L.control.groupedLayers(baseLayers, groupedOverlays).addTo(map);

        $.getJSON("<?=base_url()?>assets/badaz.geojson", function(data) {
            var ratIcon = L.icon({
                iconUrl: '<?=base_url()?>assets/Marker-1.png',
                iconSize: [36, 36],
                iconAnchor: [18, 36],
                popupAnchor: [0, -36]
            });

            L.geoJson(data, {
                pointToLayer: function(feature, latlng) {
                    var marker = L.marker(latlng, { icon: ratIcon });

                    var popupContent = "<b>" + feature.properties.NAMOBJ + "</b><br>";

                    // Define popup content directly inside the code based on the mosque name
                    if (feature.properties.NAMOBJ === "Masjid Raya Bogor") {
                        popupContent += "<i>Alamat:</i> Jl. Raya Bogor No.15, Bogor, Indonesia<br>";
                        popupContent += "<img src='assets/MasjidRayaBogor.jpg' alt='Masjid Raya Bogor' style='width: 100%; height: auto;'><br>";
                        popupContent += "<i>Deskripsi:</i> Masjid Raya Kota Bogor adalah masjid terbesar di Kota Bogor yang menjadi pusat kegiatan keagamaan dan simbol kebanggaan masyarakat Bogor. Masjid ini memiliki desain arsitektur modern yang megah dengan kapasitas besar, memungkinkan ribuan jamaah dapat melaksanakan ibadah secara bersamaan. Selain sebagai tempat ibadah, masjid ini juga digunakan untuk berbagai kegiatan sosial dan pendidikan Islam.<br>";
                        popupContent += "<i>Fasilitas:</i> Ruang shalat luas ber-AC, Area wudhu modern dan bersih, Aula serbaguna untuk kegiatan masyarakat, Perpustakaan Islami, Area parkir yang sangat luas, Taman hijau yang asri, Lift dan jalur akses untuk difabel<br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Al-ikhlas") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<i>Deskripsi:</i> <br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Nurul Maai") {
                        popupContent += "<i>Alamat:</i> Jl. Raya Bogor No.15, Bogor, Indonesia<br>";
                        popupContent += "<img src='assets/nurulmai.jpg' alt='Masjid Nurul Maai' style='width: 100%; height: auto;'><br>";
                        popupContent += "<i>Deskripsi:</i> Masjid Nurul Maai berlokasi di Jalan RE Martadinata, Kelurahan Sempur, Kecamatan Bogor Tengah, Kota Bogor. Masjid ini sering digunakan oleh karyawan PDAM dan masyarakat sekitar.<br>";
                        popupContent += "<i>Fasilitas:</i> Fasilitas yang tersedia meliputi ruang shalat yang luas, tempat wudhu modern, dan aula serbaguna untuk berbagai kegiatan keagamaan.<br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Al Baroqah") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<i>Deskripsi:</i> <br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Batu Tulis") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<img src='assets/nurulmai.jpg' alt='Masjid Nurul Maai' style='width: 100%; height: auto;'><br>";
                        popupContent += "<i>Deskripsi:</i>Masjid ini memiliki nilai sejarah tinggi dan sering menjadi tujuan wisata religi.  <br>";
                        popupContent += "<i>Fasilitas:</i> Fasilitas yang tersedia meliputi ruang shalat yang luas, tempat wudhu modern, dan aula serbaguna untuk berbagai kegiatan keagamaan.<br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Nurul Ikhlas") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<i>Deskripsi:</i> <br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Zulfa Noor") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<i>Deskripsi:</i> <br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Al Hidayah") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<i>Deskripsi:</i> <br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Baitusalam") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<i>Deskripsi:</i> <br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Asyaadah") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<i>Deskripsi:</i> <br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Balaikota") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<img src='assets/balaikota.jpg' alt='Masjid Balai' style='width: 100%; height: auto;'><br>";
                        popupContent += "<i>Deskripsi:</i>Masjid Balaikota terletak di kompleks perkantoran Pemerintah Kota Bogor. Masjid ini sering digunakan oleh pegawai pemerintah dan masyarakat sekitar untuk shalat berjamaah.  <br>";
                        popupContent += "<i>Fasilitas:</i> Fasilitas yang tersedia yaitu Tempat parkir kendaraan dinas, ruang shalat yang luas, tempat wudhu modern, dan aula serbaguna untuk berbagai kegiatan keagamaan.<br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Kejaksaan Bogor") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<img src='assets/kejaksaan.jpg' alt='Masjid Kejaksaan' style='width: 100%; height: auto;'><br>";
                        popupContent += "<i>Deskripsi:</i>Masjid ini berada di dalam kompleks Kejaksaan Negeri Bogor. Selain digunakan untuk shalat, masjid ini juga menjadi tempat kajian Islami bagi pegawai dan masyarakat sekitar. <br>";
                        popupContent += "<i>Fasilitas:</i> Fasilitas yang tersedia tempat wudhu bersih, dan aula kecil untuk kajian.<br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Kifayatul Abidin") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<i>Deskripsi:</i> <br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Annur") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<i>Deskripsi:</i> <br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Darussalam") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<i>Deskripsi:</i> <br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Amanah") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<i>Deskripsi:</i> <br>";
                    } else if (feature.properties.NAMOBJ === "Masjid Sindang Resmi") {
                        popupContent += "<i>Alamat:</i> <br>";
                        popupContent += "<i>Deskripsi:</i> <br>";
                    }

                    // Add popup to the marker
                    marker.bindPopup(popupContent);
                    return marker;
                }
            }).addTo(objk); // Add the markers to the LayerGroup objk
        });

        
        $.getJSON("assets/polybogor.geojson", function(kode) {
            L.geoJson(kode, {
                style: function(feature) {
                    var color = "#00FFFF";
                    return { color: color, weight: 2, fillOpacity: 0.2 };
                },
                onEachFeature: function(feature, layer) {
                    layer.bindPopup();
                }
            }).addTo(polygor);
        });
    });
</script>

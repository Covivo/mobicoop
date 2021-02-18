<template>
  <!-- Start of cartography experiment -->
  <v-row>
    <v-col class="col-12">
      <l-map
        ref="mmap"
        :zoom="zoom"
        :center="center"
        style="z-index:1; height:500px;"
      >
        <l-tile-layer
          :url="url"
          :attribution="attributionWithLinks"
        />
        <v-marker-cluster
          :options="clusterOptions"
        >
          <l-marker
            v-for="(point, index) in points"
            :key="index"
            :lat-lng="point.latLng"
            :draggable="markersDraggable"
            @update:latLng="updateLatLng"
            @click="clickOnPoint(point.address)"
          >
            <l-icon
              v-if="point.icon.url!==undefined"
              :icon-size="point.icon.size"
              :icon-anchor="point.icon.anchor"
              :icon-url="point.icon.url"
            />
            <l-tooltip
              v-if="point.title!==''"
            >
              <p
                class="font-weight-bold"
                v-html="point.title"
              />
              <p
                v-if="point.popup"
                id="description-tooltip"
                v-html="point.popup.description"
              />
              <MMapRelayPointDescription
                v-if="relayPoints"
                :data="point.misc"
              />
            </l-tooltip>

            <l-popup v-if="point.popup">
              <h3 v-html="point.popup.title" />
              <img
                v-if="point.popup.images && point.popup.images[0]"
                :src="point.popup.images[0]['versions']['square_100']"
                alt="avatar"
              >
              <p v-html="point.popup.description" />
              <p v-if="point.popup.date_begin && point.popup.date_end">
                {{ point.popup.date_begin }}<br> {{ point.popup.date_end }}
              </p>
            </l-popup>
          </l-marker>
        </v-marker-cluster>
        <v-dialog
          v-model="dialog"
          max-width="400"
        >
          <v-card>
            <v-card-title class="text-h5 justify-center">
              {{ $t('dialog.title') }}
            </v-card-title>
            <v-card-actions class="justify-center">
              <v-btn
                class="ml-8"
                color="primary"
                text
                @click="selectRelayPointAsOrigin "
              >
                {{ $t('dialog.origin') }}
              </v-btn>
              <v-btn
                color="primary"
                text
                @click="selectRelayPointAsDestination"
              >
                {{ $t('dialog.destination') }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <l-polyline
          v-for="(way, i) in ways"
          :key="'w'+i"
          :lat-lngs="way.latLngs"
          :color="(way.color!=='' && way.color !==undefined)?way.color:'blue'"
          @click="clickOnPolyline"
        >        
          <l-tooltip v-if="way.title !==undefined && way.title!==''">
            <p v-html="way.title" />
          </l-tooltip>
          <l-popup
            v-if="way.desc !==undefined && way.desc!==''"
          >
            <p v-html="way.desc" />
          </l-popup>
        </l-polyline>
      </l-map>
    </v-col>
  </v-row>
  <!-- end of cartography experiment -->
</template>

<script>
import L from "leaflet";
import VMarkerCluster from 'vue2-leaflet-markercluster'
import MMapRelayPointDescription from "@components/utilities/MMap/MMapRelayPointDescription"
import {messages_en, messages_fr} from "@translations/components/utilities/MMap/MMap";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    }
  },
  components: {
    VMarkerCluster,
    MMapRelayPointDescription
  },
  props: {
    provider: {
      type: String,
      default: "OpenStreetMap"
    },
    urlTiles: {
      type: String,
      default: "https://{s}.tile.osm.org/{z}/{x}/{y}.png"
    },
    providerKey: {
      // unused for the moment
      type: String,
      default: ""
    },
    attributionCopyright: {
      type: String,
      default: '{"OpenStreetMap":"https://osm.org/copyright"}'
    },
    centerDefault: {
      type: Array,
      default: function(){return [];}
    },
    zoom: {
      type: Number,
      default: 13
    },    
    typeMap: {
      type: String,
      default: ""
    },
    points: {
      type: Array,
      default: function(){return [];}
    },
    ways: {
      type: Array,
      default: function(){return [];}
    },
    markersDraggable: {
      type: Boolean,
      default: false
    },
    relayPoints: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      center: L.latLng(this.centerDefault[0], this.centerDefault[1]),
      url:this.urlTiles,
      attribution:this.attributionCopyright,
      markers:this.points,
      dialog: false,
      address: null,
      clusterOptions: {}
    };
  },
  computed: {
    attributionWithLinks(){
      let arrayAttribution = [];
      let jsonAttribution = JSON.parse(this.attribution)
      for(let contributor in jsonAttribution){
        arrayAttribution.push("<a href='"+jsonAttribution[contributor]+"' title=''>"+contributor+"</a>");
      }

      return arrayAttribution.join(', ');
    }
  },
  methods: {
    redrawMap: function() {
      // To redraw the map (when you resize the div you have to redraw the map)
      setTimeout(() => {
        this.$refs.mmap.mapObject.invalidateSize();

        // I'm using all points to set the boundaries
        let bounds = [];
        this.points.forEach((pointForBound, index) => {
          bounds.push([pointForBound.latLng.lat,pointForBound.latLng.lng]);
        });
        if(bounds.length>0){
          this.$refs.mmap.mapObject.fitBounds(bounds);
        }
      }, 100);
    },
    updateLatLng(data){
      // data contains a LatLng object.
      this.$emit("updateLatLng",data);
    },
    clickOnPolyline(data){
      // data contains a LatLng object.
      this.$emit("clickOnPolyline",data);
    },
    clickOnPoint(point){
      if (this.relayPoints) {
        this.dialog = true;
        this.address = point;
      }
    },
    selectRelayPointAsOrigin() {
      if (this.relayPoints) {
        this.$emit("SelectedAsOrigin",this.address);
        this.address= null;
        this.dialog= false;
      }
    },
    selectRelayPointAsDestination() {
      if (this.relayPoints) {
        this.$emit("SelectedAsDestination",this.address);
        this.address= null;
        this.dialog= false;

      }
    }
  }
};
</script>
<style lang="scss" scoped>
#description-tooltip{
    width: auto;
    max-width: 200px;
    overflow:hidden;
    text-overflow: ellipsis;
}
</style>
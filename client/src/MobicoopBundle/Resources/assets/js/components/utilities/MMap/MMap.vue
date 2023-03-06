<template>
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
        <!-- Markers in clusters -->
        <v-marker-cluster
          v-if="clusters"
          :options="clusterOptions"
        >
          <m-marker
            v-for="(point, index) in points"
            :key="index"
            :point="point"
            :markers-draggable="markersDraggable"
            @updateLatLng="updateLatLng"
            @clickOnPoint="clickOnPoint(point)"
          />
        </v-marker-cluster>
        <!-- Only markers, no cluster -->
        <m-marker
          v-for="(point, index) in points"
          v-else
          :key="index"
          :point="point"
          :color="point.color"
          :markers-draggable="markersDraggable"
          :circle-marker="(point.circleMarker) ? point.circleMarker : false"
          @updateLatLng="updateLatLng"
          @clickOnPoint="clickOnPoint(point)"
        />
        <v-dialog
          v-if="selectedRelayPoint && selectedRelayPoint.misc && canSelectPoint"
          v-model="dialog"
          max-width="700"
        >
          <v-card
            class="mx-auto"
            max-width="700"
          >
            <v-img
              height="150"
              :src="$t('dialog.bannerUrl')"
            />
            <v-card-title>
              <v-row>
                <v-col
                  cols="2"
                  class="mt-n12 pt-n12"
                >
                  <v-avatar
                    color="white"
                    size="65"
                  >
                    <v-img
                      :src="selectedRelayPoint.icon.url"
                      height="50"
                      width="50"
                      contain
                    />
                  </v-avatar>
                </v-col>
                <v-col justify="start">
                  <div class="secondary--text">
                    {{ $t('dialog.relayPointType.'+selectedRelayPoint.misc.type) }}
                  </div>
                </v-col>
              </v-row>
            </v-card-title>

            <v-card-text>
              <v-row>
                <v-col>
                  <div class="black--text text-h6">
                    {{ selectedRelayPoint.title }}
                  </div>
                  <div>{{ selectedRelayPoint.address.displayLabel[0] }}</div>
                  <div>{{ $t('dialog.latLng', {'lat': selectedRelayPoint.address.latitude, 'lng': selectedRelayPoint.address.longitude}) }}</div>
                </v-col>
              </v-row>
              <v-row v-if="selectedRelayPoint.misc.description">
                <v-col>
                  <div class="black--text text-h6">
                    {{ $t('dialog.moreInfos') }}
                  </div>
                  <div>
                    {{ selectedRelayPoint.misc.description }}
                  </div>
                </v-col>
              </v-row>
              <v-row justify="start">
                <v-col
                  v-if="selectedRelayPoint.misc.places"
                  cols="2"
                >
                  <v-row

                    justify="center"
                  >
                    <v-icon color="secondary">
                      mdi-account-multiple
                    </v-icon>
                  </v-row>
                  <v-row
                    justify="center"
                  >
                    <div class="secondary--text text-center">
                      {{ $t('dialog.availableSpaces') }}
                    </div>
                  </v-row>
                  <v-row
                    justify="center"
                  >
                    <div>
                      {{ selectedRelayPoint.misc.places >= 0 ? selectedRelayPoint.misc.places : 0 }}
                    </div>
                  </v-row>
                </v-col>
                <v-col
                  v-if="selectedRelayPoint.misc.placesDisabled"
                  cols="2"
                >
                  <v-row
                    justify="center"
                  >
                    <v-icon color="secondary">
                      mdi-wheelchair-accessibility
                    </v-icon>
                  </v-row>
                  <v-row
                    justify="center"
                  >
                    <div class="secondary--text text-center">
                      {{ $t('dialog.availableDisabledSpaces') }}
                    </div>
                  </v-row>
                  <v-row
                    justify="center"
                  >
                    <div>
                      {{ selectedRelayPoint.misc.placesDisabled >=0 ? selectedRelayPoint.misc.placesDisabled : 0 }}
                    </div>
                  </v-row>
                </v-col>
                <v-col
                  v-if="selectedRelayPoint.misc.free"
                  cols="2"
                >
                  <v-row
                    justify="center"
                  >
                    <v-icon color="secondary">
                      mdi-currency-eur
                    </v-icon>
                  </v-row>
                  <v-row
                    justify="center"
                  >
                    <div class="secondary--text text-center">
                      {{ $t('dialog.free') }}
                    </div>
                  </v-row>
                  <v-row
                    justify="center"
                  >
                    <div>
                      {{ $t(selectedRelayPoint.misc.free) }}
                    </div>
                  </v-row>
                </v-col>
                <v-col
                  v-if="selectedRelayPoint.misc.private"
                  cols="2"
                >
                  <v-row
                    justify="center"
                  >
                    <v-icon color="secondary">
                      mdi-shield-lock
                    </v-icon>
                  </v-row>
                  <v-row
                    justify="center"
                  >
                    <div class="secondary--text text-center">
                      {{ $t('dialog.private') }}
                    </div>
                  </v-row>
                  <v-row
                    justify="center"
                  >
                    <div>
                      {{ $t(selectedRelayPoint.misc.private) }}
                    </div>
                  </v-row>
                </v-col>
                <v-col
                  v-if="selectedRelayPoint.misc.secured"
                  cols="2"
                >
                  <v-row
                    justify="center"
                  >
                    <v-icon color="secondary">
                      mdi-boom-gate
                    </v-icon>
                  </v-row>
                  <v-row
                    justify="center"
                  >
                    <div class="secondary--text text-center">
                      {{ $t('dialog.secured') }}
                    </div>
                  </v-row>
                  <v-row
                    justify="center"
                  >
                    <div>
                      {{ $t(selectedRelayPoint.misc.secured) }}
                    </div>
                  </v-row>
                </v-col>
                <v-col
                  v-if="selectedRelayPoint.misc.official"
                  cols="2"
                >
                  <v-row
                    justify="center"
                  >
                    <v-icon color="secondary">
                      mdi-clipboard-check-outline
                    </v-icon>
                  </v-row>
                  <v-row
                    justify="center"
                  >
                    <div class="secondary--text text-center">
                      {{ $t('dialog.official') }}
                    </div>
                  </v-row>
                  <v-row
                    justify="center"
                  >
                    <div>
                      {{ $t(selectedRelayPoint.misc.official) }}
                    </div>
                  </v-row>
                </v-col>
              </v-row>
              <v-row
                class="mb-n6"
                justify="center"
              >
                <div>
                  {{ $t('dialog.selectRelayPoint') }}
                </div>
              </v-row>
            </v-card-text>

            <v-card-actions class="justify-center">
              <v-btn
                width="120"
                color="primary"

                @click="selectRelayPointAsOrigin"
              >
                {{ $t('dialog.origin') }}
              </v-btn>
              <v-btn
                width="120"
                color="primary"
                @click="selectRelayPointAsDestination"
              >
                {{ $t('dialog.destination') }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-dialog
          v-else-if="canSelectPoint"
          v-model="dialog"
          max-width="400"
        >
          <v-card>
            <v-card-title class="text-h6 justify-center">
              {{ $t('dialog.title') }}
            </v-card-title>
            <v-card-actions class="justify-center">
              <v-btn
                class="ml-8"
                color="primary"
                text
                @click="selectRelayPointAsOrigin"
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
          :dash-array="(way.dashArray) ? way.dashArray : ''"
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
</template>

<script>
import L from "leaflet";
import maxios from "@utils/maxios";
import VMarkerCluster from 'vue2-leaflet-markercluster'
import MMarker from "@components/utilities/MMap/MMarker"
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/MMap/MMap";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components: {
    VMarkerCluster,
    MMarker
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
    },
    clusters: {
      type: Boolean,
      default: true
    },
    dashArray:{
      type: String,
      default: null
    },
    territoryId: {
      type: String,
      default: null
    },
    canSelectPoint: {
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
      clusterOptions: {},
      territory: null,
      selectedRelayPoint: null
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
  mounted() {
    window.L = L;
    if (this.territoryId) {
      this.getTerritory();
    }
  },
  methods: {
    getTerritory() {
      maxios
        .post(`${this.$t("territory")}/${this.territoryId}`)
        .then(res => {
          this.territory = res.data;

          if (this.territory.minLatitude && this.territory.maxLatitude && this.territory.minLongitude && this.territory.maxLongitude) {
            this.territory.bounds = L.latLngBounds(L.latLng(this.territory.minLatitude,this.territory.minLongitude), L.latLng(this.territory.maxLatitude, this.territory.maxLongitude));
            this.redrawMap();
          }
        })
        .catch(err => console.error(err));
    },
    redrawMap: function() {
      // To redraw the map (when you resize the div you have to redraw the map)
      setTimeout(() => {
        this.$refs.mmap.mapObject.invalidateSize();

        // I'm using all points to set the boundaries
        let bounds = [];
        this.points.forEach((pointForBound, index) => {
          bounds.push([pointForBound.latLng.lat,pointForBound.latLng.lng]);
        });
        if (bounds.length === 0){
          bounds.push(this.territory.bounds);
        }
        if (bounds.length > 0) {
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
        this.address = point.address;
        this.selectedRelayPoint = point;
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

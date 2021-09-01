<template>
  <v-container>
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        lg="9"
        md="10"
        xl="6"
        class="mt-12"
      >
        <h1 class="text-h4 text-center font-weight-bold">
          {{ $t('title') }}
        </h1>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-progress-linear
          v-if="loading"
          indeterminate
          color="secondary darken-2"
        />
        <v-skeleton-loader
          v-if="loading"
          class="mx-auto"
          max-width="100%"
          type="card"
        />        
        <m-map
          v-show="!loading"
          ref="mmap"
          :points="pointsToMap"
          :provider="mapProvider"
          :url-tiles="urlTiles"
          :attribution-copyright="attributionCopyright"
          :markers-draggable="false"
          :relay-points="true"
          @SelectedAsDestination="selectedAsDestination"
          @SelectedAsOrigin="selectedAsOrigin"
        />
      </v-col>
    </v-row>
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        lg="9"
        md="10"
        xl="6"
        class="mt-6"
      >
        <h3 class="text-h5 text-center font-weight-bold">
          {{ $t('search') }}
        </h3>
      </v-col>
    </v-row>
    <v-row
      justify="center"
    >
      <search
        :default-origin="selectedOrigin"
        :default-destination="selectedDestination"
        :geo-search-url="geoSearchUrl"
        :user="user"
      />
    </v-row>
      
    <!--solidary-form-->
  </v-container>
</template>

<script>
import maxios from "@utils/maxios";
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/relayPoints/RelayPoints/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/relayPoints/RelayPoints/";
import Search from "@components/carpool/search/Search";
import MMap from "@components/utilities/MMap/MMap"
import L from "leaflet";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);  

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
  },
  components: {
    Search, MMap
  },
  props:{
    user: {
      type: Object,
      default: null
    },
    geoSearchUrl: {
      type: String,
      default: ""
    },
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
    mapProvider:{
      type: String,
      default: ""
    },
    urlTiles:{
      type: String,
      default: ""
    },
    attributionCopyright:{
      type: String,
      default: ""
    },
    urlAdmin: {
      type: String,
      default: null
    }
  },
  data () {
    return {
      search: '',
      relayPointsToMap: null,
      pointsToMap:[],
      directionWay:[],
      selectedDestination: null,
      selectedOrigin: null,
      loading:true
    }
  },
  mounted() {
    this.getRelayPoints();
  },
  methods:{
    getRelayPoints() {
      maxios
        .post(this.$t("relayPointList"))
        .then(res => {
          //console.error(res.data);
          this.relayPointsToMap = res.data;
          this.showRelayPoints();
        });
        
    },
    showRelayPoints () {
      this.pointsToMap.length = 0;
      // add relay point address to display on the map
      if (this.relayPointsToMap.length > 0) {
        this.relayPointsToMap.forEach(relayPoint => {
          let icon = null;
          if(relayPoint.relayPointType){
            if(relayPoint.relayPointType.icon && relayPoint.relayPointType.icon.url !== ""){
              icon = relayPoint.relayPointType.icon.url;
            }
          }

          let misc = {
            official:relayPoint.official,
            private:relayPoint.private,
            secured:relayPoint.secured,
            free:relayPoint.free,
            places:relayPoint.places,
            placesDisabled:relayPoint.placesDisabled,
            image:relayPoint.image
          }

          this.pointsToMap.push(this.buildPoint(relayPoint.address.latitude,relayPoint.address.longitude,relayPoint.name,relayPoint.address,icon,misc));
        });
      }
      this.loading = false;
      this.$refs.mmap.redrawMap();
    },
    buildPoint: function(lat,lng,title="",address="", icon=null, misc=null){
      let point = {
        title:title,
        latLng:L.latLng(lat, lng),
        icon: {},
        address:address,
        misc:misc
      };

      if(icon){
        point.icon = {
          size:[36,42],
          url:icon
        }
      }

      return point;
    },
    selectedAsDestination(destination) {
      console.error(destination);
      this.selectedDestination = destination;
    },
    selectedAsOrigin(origin) {
      console.error(origin);
      this.selectedOrigin = origin;
    }
  }
}
</script>

<style lang="scss" scoped>
.multiline {
  padding:20px;
  white-space: normal;
}
.vue2leaflet-map {
    z-index: 1;
}

</style>
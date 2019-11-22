<template>
  <v-content>
    <!--SnackBar-->
    <v-snackbar
      v-model="snackbar"
      :color="(errorUpdate)?'error':'warning'"
      top
    >
      <!--      {{ (errorUpdate)?textSnackError:textSnackOk }}-->
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>

    <v-container>
      <!-- community buttons and map -->
      <v-row
        justify="center"
      >
        <v-col
          cols="12"
          md="8"
          xl="6"
          align="center"
        >
          <!-- Community : avatar, title and description -->
          <event-infos
            :event="event"
            :url-alt-avatar="urlAltAvatar"
            :avatar-version="avatarVersion"
          />
          <!-- event buttons and map -->
          <v-row
            align="center"
          >
            <v-col
              cols="4"
              class="text-center"
            >
              <!-- button  -->
              <div>
                <v-btn
                  color="secondary"
                  rounded
                  @click="publish"
                >
                  {{ $t('buttons.publish.label') }}
                </v-btn>
                <v-btn
                  color="secondary"
                  rounded
                  @click="publish"
                >
                  {{ $t('buttons.widget.label') }}
                </v-btn>
              </div>
              <!-- button if user ask to join community but is not accepted yet -->
            </v-col>
            <!-- map -->
            <v-col
              cols="8"
            >
              <v-card
                v-show="loadingMap"
                flat
                align="center"
                height="500"
                color="backSpiner"
              >
                <v-progress-circular
                  size="250"
                  indeterminate
                  color="tertiary"
                />
              </v-card>
              <m-map
                v-show="!loadingMap"
                ref="mmap"
                :points="pointsToMap"
                :provider="mapProvider"
                :url-tiles="urlTiles"
                :attribution-copyright="attributionCopyright"
              />
            </v-col>
          </v-row>
        </v-col>
      </v-row>
      <!-- search journey -->
      <v-row
        justify="center"
        align="center"
      >
        <v-col
          cols="6"
          class="mt-6"
        >
          <p class="headline">
            {{ $t('title.searchCarpool') }}
          </p>
        </v-col>
      </v-row>
      <v-row
        align="center"
        justify="center"
      >
        <search
          :geo-search-url="geodata.geocompleteuri"
          :user="user"
          :params="params"
          :punctual-date-optional="punctualDateOptional"
          :regular="regular"
          :init-destination="destination"
        />
      </v-row>
    </v-container>
  </v-content>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/event/Event.json";
import TranslationsClient from "@clientTranslations/components/event/Event.json";
import EventInfos from "@components/event/EventInfos";
import Search from "@components/carpool/search/Search";
import MMap from "@components/utilities/MMap"
import L from "leaflet";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
    EventInfos, Search, MMap
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props:{
    user: {
      type: Object,
      default: null
    },
    geodata: {
      type: Object,
      default: null
    },
    users: {
      type: Array,
      default: null
    },
    event:{
      type: Object,
      default: null
    },
    lastUsers: {
      type: Array,
      default: null
    },
    avatarVersion: {
      type: String,
      default: null
    },
    urlAltAvatar: {
      type: String,
      default: null
    },
    regular: {
      type: Boolean,
      default: false
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
    initDestination: {
      type: Object,
      default: null
    },
    initOrigin: {
      type: Object,
      default: null
    },
    points: {
      type: Array,
      default: null
    },
  },
  data () {
    return {
      destination: '',
      origin: this.initOrigin,
      search: '',
      pointsToMap:[],
      directionWay:[],
      loading: false,
      snackbar: false,
      // textSnackOk: this.$t("snackbar.joinCommunity.textOk"),
      // textSnackError: this.$t("snackbar.joinCommunity.textError"),
      errorUpdate: false,
      loadingMap: false,
      params: { 'eventId' : this.event.id },

    }
  // Link the event in the adresse
  },created: function () {
    this.$set(this.initDestination, 'event', this.event);
    this.destination = this.initDestination;
  },
  mounted() {
    this.showPoints()
  },
  methods:{
    searchChanged: function (search) {
      this.origin = search.origin;
      this.destination = search.destination;
      this.dataRegular = search.regular;
      this.date = search.date;
    },
    post: function (path, params, method='post') {
      const form = document.createElement('form');
      form.method = method;
      form.action = window.location.origin+'/'+path;

      for (const key in params) {
        if (params.hasOwnProperty(key)) {
          const hiddenField = document.createElement('input');
          hiddenField.type = 'hidden';
          hiddenField.name = key;
          hiddenField.value = params[key];
          form.appendChild(hiddenField);
        }
      }
      document.body.appendChild(form);
      form.submit();
    },
    publish() {
      let lParams = {
        origin: JSON.stringify(this.destination),
        destination: JSON.stringify(this.destination),
        regular: null,
        date: null,
        time: null,
        ...this.params
      };
      this.post(`${this.$t("buttons.publish.route")}`, lParams);
    },
    getEventProposals () {

      this.loadingMap = true;
      axios

        .get('/event-proposals/'+this.event.id,
          {
            headers:{
              'content-type': 'application/json'
            }
          })
        .then(res => {
          this.errorUpdate = res.data.state;
          this.pointsToMap.length = 0;
          // add the community address to display on the map
          if (this.event.address) {
            this.pointsToMap.push(this.buildPoint(this.event.address.latitude,this.event.address.longitude,this.event.name));
          }

          // add all the waypoints of the event to display on the map :
          res.data.forEach((waypoint, index) => {
            this.pointsToMap.push(this.buildPoint(waypoint.latLng.lat,waypoint.latLng.lon,waypoint.title));
          });
          this.loadingMap = false;
          setTimeout(this.$refs.mmap.redrawMap(),600);

        });
    },

    showPoints () {
      this.pointsToMap.length = 0;
      // add the event address to display on the map
      if (this.event.address) {
        this.pointsToMap.push(this.buildPoint(this.event.address.latitude,this.event.address.longitude,this.event.name));
      }

      // add all the waypoints of the event to display on the map :
      this.points.forEach((waypoint, index) => {
        this.pointsToMap.push(this.buildPoint(waypoint.latLng.lat,waypoint.latLng.lon,waypoint.title));
      });
      this.loadingMap = false;
      setTimeout(this.$refs.mmap.redrawMap(),600);
    },
    buildPoint: function(lat,lng,title="",pictoUrl="",size=[],anchor=[]){
      let point = {
        title:title,
        latLng:L.latLng(lat, lng),
        icon: {}
      }

      if(pictoUrl!==""){
        point.icon = {
          url:pictoUrl,
          size:size,
          anchor:anchor
        }
      }

      return point;
    }
  }
}
</script>

<template>
  <div>
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
      <!-- event buttons and map -->
      <v-row
        justify="center"
      >
        <v-col
          cols="12"
          md="8"
          xl="6"
          align="center"
        >
          <!-- event : avatar, title and description -->
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
                  class="mt-1"
                  color="secondary"
                  rounded
                  :href="$t('buttons.widget.route') + event.id"
                >
                  {{ $t('buttons.widget.label') }}
                </v-btn>
                <EventReport
                  class="mt-1"
                  :event="event"
                />
              </div>
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
          :hide-publish="true"
          :default-destination="defaultDestination"
          :disable-search="disableSearch"
        />
      </v-row>
    </v-container>
  </div>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/event/Event.json";
import TranslationsClient from "@clientTranslations/components/event/Event.json";
import EventInfos from "@components/event/EventInfos";
import EventReport from "@components/event/EventReport";
import Search from "@components/carpool/search/Search";
import MMap from "@components/utilities/MMap"
import L from "leaflet";
import moment from "moment";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
    EventReport, EventInfos, Search, MMap
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
      isLogged: false,
      loadingMap: false,
      params: { 'eventId' : this.event.id },
      defaultDestination: this.event.address,
    }
  },
  computed: {
    disableSearch() {
      let now = moment();
      if (now > moment(this.event.toDate.date))
        return true;
      else
        return false;
    }
  // Link the event in the adresse
  },created: function () {
    this.$set(this.initDestination, 'event', this.event);
    this.destination = this.initDestination;
  },
  mounted() {
    this.showPoints();
    // this.getEventProposals();
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
    checkIfUserLogged() {
      if (this.user !== null) {
        this.isLogged = true;
      }
    },
    checkDomain() {
      if (this.event.validationType == 2) {
        let mailDomain = (this.user.email.split("@"))[1];
        if (!(this.event.domain.includes(mailDomain))) {
          return this.domain = false;
        }
      }
    },
    publish() {
      let lParams = {
        origin: null,
        destination: JSON.stringify(this.destination),
        regular: null,
        date: null,
        time: null,
        ...this.params
      };
      this.post(`${this.$t("buttons.publish.route")}`, lParams);
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

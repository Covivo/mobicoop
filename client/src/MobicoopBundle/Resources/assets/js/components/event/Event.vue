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
          lg="9"
          md="10"
          xl="6"
          class="text-center"
        >
          <!-- event : avatar, title and description -->
          <event-infos
            :event="event"
            :url-alt-avatar="urlAltAvatar"
            :avatar-version="avatarVersion"
          />
          <!-- event buttons and map -->
          <v-row
            class="text-center"
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
                  class="mt-3"

                  color="primary"
                  rounded
                  :href="$t('buttons.widget.route') + event.id"
                >
                  {{ $t('buttons.widget.label') }}
                </v-btn>
                <EventReport
                  class="mt-3"
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
                class="text-center"
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
                :ways="directionWay"
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
      >
        <v-col
          cols="12"
          lg="9"
          md="10"
          xl="6"
          class="text-center mt-6"
        >
          <h3 class="headline text-justify font-weight-bold">
            {{ $t('title.searchCarpool') }}
          </h3>
        </v-col>
      </v-row>
      <v-row
        class="text-center"
        justify="center"
      >
        <search
          :geo-search-url="geodata.geocompleteuri"
          :user="user"
          :params="params"
          :punctual-date-optional="punctualDateOptional"
          :regular="regular"
          :hide-publish="true"
          :default-destination="defaultDestination"
          :disable-search="disableSearch"
        />
      </v-row>
    </v-container>
  </div>
</template>
<script>

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
      locale: this.$i18n.locale,
      destination: '',
      origin: this.initOrigin,
      search: '',
      pointsToMap:[],
      directionWay:[],
      loading: false,
      snackbar: false,
      errorUpdate: false,
      isLogged: false,
      loadingMap: false,
      params: { 'eventId' : this.event.id },
      defaultDestination: this.initDestination,
      regular: false,
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
  },
  created: function () {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
    this.$set(this.initDestination, 'event', this.event);
    this.destination = this.initDestination;
  },
  mounted() {
    this.showEventProposals();
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

    showEventProposals () {
      this.pointsToMap.length = 0;
      // add the event address to display on the map
      if (this.event.address) {
        this.pointsToMap.push(this.buildPoint(this.event.address.latitude,this.event.address.longitude,this.event.name,"/images/cartography/pictos/destination.png",[36, 42]));
      }
          
      // add all the waypoints of the event to display on the map
      // We draw straight lines between those points
      // if the user is already accepted or if the doesn't hide members or proposals to non members.
      this.points.forEach((proposal, index) => {
        let currentProposal = {latLngs:[]};
        let infosForPopUp = {
          origin:'',
          destination:'',
          originLat:null,
          originLon:null,
          destinationLat:null,
          destinationLon:null,
          carpoolerFirstName:"",
          carpoolerLastName:""
        };

        if(proposal.type !== 'return'){ // We show only outward or one way proposals

          infosForPopUp.carpoolerFirstName = proposal.carpoolerFirstName;
          infosForPopUp.carpoolerLastName = proposal.carpoolerLastName;

          // We build the content of the popup
          currentProposal.desc = "<p style='text-align:center;'><strong>"+infosForPopUp.carpoolerFirstName+" "+infosForPopUp.carpoolerLastName+"</strong></p>"


          proposal.waypoints.forEach((waypoint, index) => {
            currentProposal.latLngs.push(waypoint.latLng);
            infosForPopUp.origin = waypoint.title;
            infosForPopUp.originLat = waypoint.latLng.lat;
            infosForPopUp.originLon = waypoint.latLng.lon;
            this.pointsToMap.push(this.buildPoint(waypoint.latLng.lat,waypoint.latLng.lon,currentProposal.desc,"",[],[],"<p>"+waypoint.title+"</p>"));
          });


          currentProposal.desc += "<p style='text-align:left;'><strong>"+this.$t('map.origin')+"</strong> : "+infosForPopUp.origin+"<br />";
          if(proposal.frequency=='regular') currentProposal.desc += "<em>"+this.$t('map.regular')+"</em>";

          // And now the content of a tooltip (same as popup but without the button)
          currentProposal.title = currentProposal.desc;
                
          // We add the button to the popup (To Do: Button isn't functionnal. Find a good way to launch a research)
          //currentProposal.desc += "<br /><button type='button' class='v-btn v-btn--contained v-btn--rounded theme--light v-size--small secondary overline'>"+this.$t('map.findMatchings')+"</button>";

          // We are closing the two p
          currentProposal.title += "</p>";
          currentProposal.desc += "</p>";

          // We set the destination before the push to directinWay. It's the address of the event
          let destination = {
            "lat":this.event.address.latitude,
            "lon":this.event.address.longitude
          }
          currentProposal.latLngs.push(destination);

          this.directionWay.push(currentProposal);

        }
      });
      this.$refs.mmap.redrawMap();
    },
    buildPoint: function(lat,lng,title="",pictoUrl="",size=[],anchor=[],popupDesc=""){
      let point = {
        title:title,
        latLng:L.latLng(lat, lng),
        icon: {},
      };

      if(pictoUrl!==""){
        point.icon = {
          url:pictoUrl,
          size:size,
          anchor:anchor
        }
      }

      if(popupDesc!==""){
        point.popup = {
          title:title,
          description:popupDesc
        }
      }
      return point;
    }
  }
}
</script>

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
          class="text-justify"
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
                  v-if="!eventPassed"
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
                  :href="$t('buttons.widget.route', {'id':event.id,'urlKey':event.urlKey})"
                >
                  {{ $t('buttons.widget.label') }}
                </v-btn>
                <Report
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
        v-if="!eventPassed"
        justify="center"
      >
        <v-col
          cols="12"
          lg="9"
          md="10"
          xl="6"
          class="text-center mt-6"
        >
          <h3 class="text-h5 text-justify font-weight-bold">
            {{ $t('title.searchCarpool') }}
          </h3>
        </v-col>
      </v-row>
      <v-row
        v-if="!eventPassed"
        class="text-center"
        justify="center"
      >
        <search
          :geo-search-url="geodata.geocompleteuri"
          :user="user"
          :params="params"
          :punctual-date-optional="punctualDateOptional"
          :regular="regular"
          :default-destination="defaultDestination"
          :publish-button-always-active="publishButtonAlwaysActive"
        />
      </v-row>
    </v-container>
    <LoginOrRegisterFirst
      :id="lEventId"
      :show-dialog="loginOrRegisterDialog"
      type="event"
      @closeLoginOrRegisterDialog="loginOrRegisterDialog = false "
    />
  </div>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/event/Event/";
import EventInfos from "@components/event/EventInfos";
import Report from "@components/utilities/Report";
import Search from "@components/carpool/search/Search";
import LoginOrRegisterFirst from '@components/utilities/LoginOrRegisterFirst';
import MMap from "@components/utilities/MMap/MMap"
import L from "leaflet";
import moment from "moment";

export default {
  components: {
    Report, 
    EventInfos, 
    Search, 
    MMap,
    LoginOrRegisterFirst
  },
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
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
    publishButtonAlwaysActive:{
      type: Boolean,
      default:false
    }
  },
  data () {
    return {
      locale: localStorage.getItem("X-LOCALE"),
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
      eventPassed: false,
      loginOrRegisterDialog: false,
      lEventId: this.event.id ? this.event.id : null,
    }
  },
  computed: {
    
  // Link the event in the adresse
  },
  created: function () {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
    this.$set(this.initDestination, 'event', this.event);
    this.destination = this.initDestination;
  },
  mounted() {
    this.showEventProposals();
    this.checkIfEventIsPassed();
    this.checkIfUserLogged();
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
      if (this.isLogged){
        let lParams = {
          origin: null,
          destination: JSON.stringify(this.destination),
          regular: null,
          date: null,
          time: null,
          ...this.params
        };
        this.post(`${this.$t("buttons.publish.route", {id: this.lEventId})}`, lParams);
      } else {
        this.loginOrRegister();
      }
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


        infosForPopUp.carpoolerFirstName = proposal.carpoolerFirstName;
        infosForPopUp.carpoolerLastName = proposal.carpoolerLastName;

        // We build the content of the popup
        currentProposal.desc = "<p style='text-align:center;'><strong>"+infosForPopUp.carpoolerFirstName+" "+infosForPopUp.carpoolerLastName+"</strong></p>"


        proposal.waypoints.forEach((waypoint, index) => {
          currentProposal.latLngs.push(waypoint.latLng);
          if(index==0){
            infosForPopUp.origin = waypoint.title;
            infosForPopUp.originLat = waypoint.latLng.lat;
            infosForPopUp.originLon = waypoint.latLng.lon;
          }
          this.pointsToMap.push(this.buildPoint(waypoint.latLng.lat,waypoint.latLng.lon,currentProposal.desc,"",[],[],"<p>"+waypoint.title+"</p>"));
        });


        currentProposal.desc += "<p style='text-align:left;'><strong>"+this.$t('map.origin')+"</strong> : "+infosForPopUp.origin+"<br />";
        if(proposal.frequency=='regular') currentProposal.desc += "<em>"+this.$t('map.regular')+"</em>";

        // And now the content of a tooltip (same as popup but without the button)
        currentProposal.title = currentProposal.desc;
                
        // We add the button to the popup (To Do: Button isn't functionnal. Find a good way to launch a research)
        //currentProposal.desc += "<br /><button type='button' class='v-btn v-btn--contained v-btn--rounded theme--light v-size--small secondary text-overline'>"+this.$t('map.findMatchings')+"</button>";

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
    },

    checkIfEventIsPassed() {
      let now = moment();
      if (now > moment(this.event.toDate.date)) {
        this.eventPassed = true;
      }  
    },
    loginOrRegister() {
      this.loginOrRegisterDialog = true;
    }
  }
}
</script>

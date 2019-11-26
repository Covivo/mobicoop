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
      <!-- eventWidget buttons and map -->
      <v-row
        justify="center"
      >
        <v-col
          cols="12"
          align="center"
        >
          <!-- Event : avatar, title and description -->
          <event-infos
            :event="event"
            :url-alt-avatar="urlAltAvatar"
            :avatar-version="avatarVersion"
            :display-description="false"
          />
        </v-col>
      </v-row>
      <!-- search journey -->
      <p class="mb-0">
        {{ $t('title.searchCarpool') }}
      </p>
      <!-- event buttons and map -->
      <v-row
        align="center"
        justify="center"
      >
        <v-col
          col="12"
        >
          <search
            :geo-search-url="geodata.geocompleteuri"
            :user="user"
            :params="params"
            :punctual-date-optional="punctualDateOptional"
            :regular="regular"
            :default-destination="defaultDestination"
            :hide-publish="true"
            :disable-search="disableSearch"
            :show-destination="false"
            :is-widget="true"
          />
        </v-col>
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
// import MMap from "@components/utilities/MMap"
import L from "leaflet";
import moment from "moment";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
    EventInfos, Search,
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
  },
  data () {
    return {
      search: '',
      headers: [
        {
          text: 'Id',
          align: 'left',
          sortable: false,
          value: 'id',
        },
        { text: 'Nom', value: 'familyName' },
        { text: 'Prenom', value: 'givenName' },
        { text: 'Telephone', value: 'telephone' },
      ],
      pointsToMap:[],
      directionWay:[],
      loading: false,
      snackbar: false,
      errorUpdate: false,
      isAccepted: false,
      askToJoin: false,
      checkValidation: false,
      isLogged: false,
      loadingMap: false,
      domain: true,
      refreshMemberList: false,
      refreshLastUsers: false,
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
  },
  mounted() {
    this.getEventProposals();
    this.checkDomain();
  },
  methods:{
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
          // add the event address to display on the map
          if (this.event.address) {
            this.pointsToMap.push(this.buildPoint(this.event.address.latitude,this.event.address.longitude,this.event.name));
          }

          // add all the waypoints of the event to display on the map :
          res.data.forEach((waypoint, index) => {
            this.pointsToMap.push(this.buildPoint(waypoint.latLng.lat,waypoint.latLng.lon,waypoint.title));
          });
          this.loadingMap = false;
          // setTimeout(this.$refs.mmap.redrawMap(),600);

        });
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
    },
    contact: function(data){
      const form = document.createElement('form');
      form.method = 'post';
      form.action = this.$t("buttons.contact.route");

      const params = {
        carpool:0,
        idRecipient:data.id,
        familyName:data.familyName,
        givenName:data.givenName
      }

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
    membersListRefreshed(){
      this.refreshMemberList = false;
    },
    lastUsersRefreshed(){
      this.refreshLastUsers = false;
    }

  }
}
</script>

<style>
  div {
    padding: 0px 3px !important;
  }
 </style>
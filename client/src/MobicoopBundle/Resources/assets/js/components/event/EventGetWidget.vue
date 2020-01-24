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
      <!-- eventGetWidget buttons and map -->
      <v-row
        justify="center"
      >
        <v-col
          cols="4"
          align="center"
        >
          <iframe
            :src="`/evenement-widget/${event.id}`"
            width="100%"
            height="640px"
            frameborder="0"
            scrolling="no"
          />
        </v-col>
        <v-col
          cols="8"
          class="mt-12"
        >
          <v-row class="mt-12">
            <h4>Intégrer le widget</h4>
            <p class="mt-8">
              Pour intégrer le widget, il faut copier le texte ci-dessous et le coller sur votre site web.<br>
              Vous pouvez modifier les éléments en gras afin de personnaliser votre widget.
            </p>
            <p>
              &lt;iframe src="{{ getUrl() }}" width="<strong>100%</strong>" height="<strong>440px</strong>" frameborder="0" scrolling="no"&gt;&lt;/iframe&gt;
            </p>
            <p><strong>Attention</strong> : Certains outils de publication comme Wordpress nécessitent l'ajout de plugins spécifiques pour pouvoir utiliser une iFrame.</p>
          </v-row>
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
// import EventInfos from "@components/event/EventInfos";
// import Search from "@components/carpool/search/Search";
// import MMap from "@components/utilities/MMap"
import L from "leaflet";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
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

    }
  },
  mounted() {
    // this.getCommunityUser();
    // this.checkIfUserLogged();
    // this.getEventProposals();
    // this.checkDomain();
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
    getCommunityUser() {
      if(this.user){
        this.checkValidation = true;
        axios
          .post(this.$t('urlCommunityUser'),{communityId:this.community.id, userId:this.user.id})
          .then(res => {
            if (res.data.length > 0) {
              this.isAccepted = res.data[0].status == 1;
              this.askToJoin = true
            }
            this.checkValidation = false;

          });
      }
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
    getUrl() {
      return window.location.protocol +"//"+ window.location.host + "/evenement-widget/" + this.event.id;
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

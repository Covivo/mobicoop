<template>
  <div>
    <v-snackbar
      v-model="showSendSuccess"
      color="success"
      top
    >
      <v-icon>mdi-check-circle-outline</v-icon> {{ $t('externalResult.contact.return.ok') }}
    </v-snackbar>
    <v-snackbar
      v-model="showSendError"
      color="error"
      top
    >
      <v-icon>mdi-close-circle-outline</v-icon> {{ $t('externalResult.contact.return.error') }}
    </v-snackbar>
    <v-row
      align="center"
      dense
    >
      <!-- Carpooler identity -->
      <v-col
        cols="4"
        md="3"
        lg="3"
      >
        <carpooler-identity
          :carpooler="carpooler"
          :age-display="ageDisplay"
        />
      </v-col>

      <!-- Community -->
      <v-col
        v-if="enabled"
        align="left"
        cols="2"
        md="3"
        lg="3"
      >
        <v-tooltip
          v-for="community in communities"
          :key="community.id"
          color="info"
          right
        >
          <template v-slot:activator="{ on }">
            <v-list-item-avatar
              class="grey lighten-2 ml-1 mr-1"
              contain
              :size="avatarSize" 
              v-on="on"
            >
              <v-img
                v-if="community.image[0]"
                :src="community.image[0]['versions']['square_100']"
                alt="avatar"
              />
              <v-img
                v-else
                class="grey lighten-2"
                src="/images/avatarsDefault/avatar.svg"
                alt="avatar"
              />
            </v-list-item-avatar>
          </template>
          <span>
            {{ community.name }}</span> 
        </v-tooltip>
      </v-col>
      <v-col
        v-else
        cols="2"
        md="3"
        lg="3"
      />

      <!-- Carpooler contact -->
      <v-col
        cols="4"
        md="2"
        lg="3"
      >
        <carpooler-contact
          :carpooler="carpooler"
          :user="user"
        />
      </v-col>


      <!-- Carpool button -->
      <v-col
        v-if="!externalRdexJourneys"
        cols="2"
        lg="3"
        class="text-right"
      >
        <v-btn
          rounded
          color="secondary"
          large
          @click="emitCarpoolEvent"
        >
          <span>
            {{ $t('seeDetails') }}
          </span>
        </v-btn>
      </v-col>
      <v-col
        v-else
        cols="2"
        lg="3"
        class="text-right"
      >
        <v-btn
          rounded
          color="secondary"
          type="button"
          :href="externalUrl"
          target="_blank"
          class="mt-1"
        >
          <span>
            {{ $t('externalResult.go') }}
          </span>
        </v-btn>
        <v-tooltip bottom>
          <template v-slot:activator="{ on }">
            <div
              class="ma-0 pa-0"
              v-on="on"
            >
              <v-btn
                :disabled="user == null"
                rounded
                color="primary"
                type="button"
                target="_blank"
                class="mt-1"
                @click="externalContactModal"
              >
                <span>
                  {{ $t('externalResult.contact.button.label') }}
                </span>
              </v-btn>
            </div>
          </template>
          <span>{{ $t('externalResult.contact.button.tooltip') }}</span>
        </v-tooltip>
        <v-card-text class="py-0">
          <em>{{ externalOrigin }}</em>
        </v-card-text>
      </v-col>  
    </v-row>
    
    <v-dialog
      v-model="dialogExternalContact"
      width="80%"
      min-height="500px"
    >
      <v-card>
        <v-card-title class="headline grey lighten-2">
          {{ $t('externalResult.contact.popup.title') }}
        </v-card-title>

        <v-card-text>
          <p>{{ $t('externalResult.contact.popup.intro', {origin:externalOrigin}) }}.</p>
          <p>
            {{ $t('externalResult.contact.popup.instructions.line1') }}.<br>
            {{ $t('externalResult.contact.popup.instructions.line2') }}.
          </p>
        </v-card-text>
        <v-card-text>
          <v-textarea
            v-model="content"
            name="input-7-1"
            :label="$t('externalResult.contact.popup.textarea.label')"
            :value="defaultTextContact"
            rows="9"
          />
          <p class="text-right">
            <v-btn
              rounded
              color="primary"
              :loading="loadingSendContact"
              @click="externalContactSend"
            >
              {{ $t('externalResult.contact.popup.send') }}
            </v-btn>
          </p>
        </v-card-text>
        <v-divider />
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="error"
            text
            @click="dialogExternalContact = false"
          >
            {{ $t('externalResult.contact.popup.cancel') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import axios from "axios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/utilities/CarpoolerSummary/";
import CarpoolerIdentity from "./CarpoolerIdentity";
import CarpoolerContact from "./CarpoolerContact";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  components: {
    CarpoolerIdentity,
    CarpoolerContact
  },
  props: {
    origin: {
      type: Object,
      default: null
    },
    destination: {
      type: Object,
      default: null
    },
    proposal: {
      type: Object,
      default: null
    },
    carpooler: {
      type: Object,
      default: null
    },
    user: {
      type: Object,
      default: null
    },
    externalRdexJourneys: {
      type: Boolean,
      default: true
    },
    externalUrl: {
      type: String,
      default: null
    },    
    externalOrigin: {
      type: String,
      default: null
    },
    externalProvider: {
      type: String,
      default: null
    },
    externalJourneyId: {
      type: String,
      default: null
    },
    communities: {
      type: Array,
      default: null
    },
    ageDisplay: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      connected: null !== this.user,
      dialogExternalContact: false,
      loadingSendContact: false,
      content:"",
      showSendError: false,
      showSendSuccess: false,
      enabled: (null !== this.communities) ? Object.keys(this.communities).length > 0 : null
    };
  },
  computed: {
    avatarSize() {
      switch (this.$vuetify.breakpoint.name) {
      case "md":
        return '20';
      case "lg":
        return '30';
      case "xl":
        return '35';
      default:
        return '20';
      } 
    },
    defaultTextContact(){

      if(this.user==null) return null;

      let text = this.$t('externalResult.contact.popup.textarea.content.hello')+" "+this.carpooler.givenName+"\n\n";
      text += this.$t('externalResult.contact.popup.textarea.content.carpool',{origin:this.origin.addressLocality,destination:this.destination.addressLocality})+".\n";
      text += this.$t('externalResult.contact.popup.textarea.content.name',{name:this.user.givenName+" "+this.user.shortFamilyName})+"\n";
      if(this.user.phoneDisplay==1) text += this.$t('externalResult.contact.popup.textarea.content.phone',{phone:this.user.telephone})+".\n";
      text += this.$t('externalResult.contact.popup.textarea.content.email',{email:this.user.email})+".\n\n";
      text += this.$t('externalResult.contact.popup.textarea.content.seeya')+" !";
      return text;
    }
  },
  created(){
    this.content = this.defaultTextContact;
  },
  methods: {
    buttonAlert(msg, e) {
      alert(msg);
    },
    emitCarpoolEvent: function() {
      if (this.connected) {
        this.$emit("carpool");
      } else {
        this.$emit("loginOrRegister");
      }
    },
    externalContactModal(){
      this.dialogExternalContact = true;
    },
    externalContactSend(){
      this.loadingSendContact = true;

      // ROLE is always passenger for now. See Matchings.vue, we search only driver by RDEX
      let params = {
        provider: this.externalProvider,
        role: 2,
        carpoolerUuid: this.carpooler.id,
        journeysUuid: this.externalJourneyId,
        content: this.content
      };

      axios.post(this.$t("externalResult.contact.urlSendContact"),params)
        .then(response => {
          // console.error(response.data);
          this.loadingSendContact = false;
          this.dialogExternalContact = false;

          // Message ok or error
          (response.data.error) ? this.showSendError = true : this.showSendSuccess = true;
          
        })
        .catch(function (error) {
          console.error(error);
        });     
    }
  }
};
</script>
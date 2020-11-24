<template>
  <div>
    <v-row
      align="center"
      dense
    >
      <!-- Carpooler identity -->
      <v-col
        cols="4"
      >
        <carpooler-identity
          :carpooler="carpooler"
        />
      </v-col>
  
      <!-- Carpooler rate -->
      <v-col
        v-if="carpoolerRate"
        cols="1"
      >
        <v-tooltip
          bottom
          color="info"
        >
          <template v-slot:activator="{ on }">
            <v-container
              class="pt-0 pb-0 pl-0 pr-0"
            >
              <v-row
                align="center"
                dense
                v-on="on"
              >
                <span
                  class="yellow--text text--darken-2"
                >
                  4.7
                </span>

                <v-icon
                  :color="'yellow darken-2'"
                  class="ml-1"
                >
                  mdi-star
                </v-icon>
              </v-row>
            </v-container>
          </template>
          <span> {{ $t('inDev') }} </span>
        </v-tooltip>
      </v-col>

      <!-- Carpooler contact -->
      <v-col
        cols="4"
      >
        <carpooler-contact
          :carpooler="carpooler"
          :user="user"
        />
      </v-col>


      <!-- Carpool button -->
      <v-col
        v-if="!externalRdexJourneys"
        cols="3"
        class="text-right"
      >
        <v-btn
          rounded
          color="secondary"
          large
          @click="emitCarpoolEvent"
        >
          <span>
            {{ $t('carpool') }}
          </span>
        </v-btn>
      </v-col>
      <v-col
        v-else
        cols="3"
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
            {{ $t('externalResult.contact.button') }}
          </span>
        </v-btn>
        <br>
        <v-card-text class="py-0">
          <em>{{ externalOrigin }}</em>
        </v-card-text>
      </v-col>  
    </v-row>

    <!-- Community -->
    <v-row 
      v-if="communities" 
      no-gutters
    >
      <v-col cols="4" />
      <v-col
        align="left"
        cols="5"
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
      <v-col cols="3" />
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
import {messages_en, messages_fr} from "@translations/components/carpool/utilities/CarpoolerSummary/";
import CarpoolerIdentity from "./CarpoolerIdentity";
import CarpoolerContact from "./CarpoolerContact";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
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
    carpoolerRate: {
      type: Boolean,
      default: true
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
    communities: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      connected: this.user !== null,
      dialogExternalContact: false,
      loadingSendContact: false
    };
  },
  computed: {
    avatarSize() {
      switch (this.$vuetify.breakpoint.name) {
      case "xs":
        return '20';
      case "sm":
        return '20';
      case "md":
        return '20';
      case "lg":
        return '35';
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
    }
  }
};
</script>
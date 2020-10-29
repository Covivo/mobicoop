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
          large
          type="button"
          :href="externalUrl"
          target="_blank"
          class="mt-1"
        >
          <span>
            {{ $t('externalUrl') }}
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
    communities: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      connected: this.user !== null,
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
    }
  }
};
</script>
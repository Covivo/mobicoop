<template>
  <div>
    <v-card style="overflow:hidden">
      <v-toolbar color="primary">
        <v-toolbar-title class="toolbar">
          {{ $t("detailTitle") }}
        </v-toolbar-title>

        <v-spacer />

        <v-btn
          icon
          @click="$emit('close')"
        >
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>
      <v-row dense>
        <v-col cols="12">
          <!-- Journey Details -->

          <v-row dense>
            <v-col cols="12">
              <v-card-text>
                <!-- Date / seats / price -->
                <v-row
                  align="center"
                  dense
                >
                  <!-- Date -->
                  <v-col
                    cols="9"
                    class="text-h6 text-center"
                  >
                    {{ computedDate }}
                  </v-col>
                  <!-- Price -->
                  <v-col
                    cols="3"
                    class="text-h6 text-center"
                  >
                    {{
                      result.roundedPrice
                        ? result.roundedPrice + "€"
                        : ""
                    }}
                  </v-col>
                </v-row>

                <!-- Route / carpooler -->
                <v-row
                  align="start"
                  dense
                >
                  <!-- Route -->
                  <v-col cols="12">
                    <v-row dense>
                      <v-col>
                        <v-timeline
                          dense
                        >
                          <v-timeline-item
                            :color="'primary'"
                            fill-dot
                          >
                            <template v-slot:icon>
                              <v-avatar>
                                <v-icon
                                  color="white"
                                >
                                  mdi-human-greeting
                                </v-icon>
                              </v-avatar>
                            </template>
                            <v-row dense>
                              <!-- <v-col

                                cols="3"
                                class="text-left"
                              >
                                <span class="font-weight-bold">15:30</span>
                              </v-col> -->
                              <v-col
                                cols="9"
                                class="text-left"
                              >
                                <span class="font-weight-bold">{{ result.origin.streetAddress }}</span>
                              </v-col>
                            </v-row>
                          </v-timeline-item>
                          <v-timeline-item
                            :color="'primary'"
                            fill-dot
                          >
                            <template v-slot:icon>
                              <v-avatar>
                                <v-icon
                                  color="white"
                                >
                                  mdi-flag
                                </v-icon>
                              </v-avatar>
                            </template>
                            <v-row dense>
                              <!-- <v-col
                                cols="3"
                                class="text-left"
                              >
                                <span class="font-weight-bold">15:30</span>
                              </v-col> -->
                              <v-col
                                cols="9"
                                class="text-left"
                              >
                                <span class="font-weight-bold">{{ result.destination.streetAddress }}</span>
                              </v-col>
                            </v-row>
                          </v-timeline-item>
                        </v-timeline>
                      </v-col>
                    </v-row>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
      <!-- end Journey details and carpooler -->
      <!-- Action buttons -->
      <v-card-actions>
        <v-btn
          color="secondary"
          @click="booking(result)"
        >
          {{ $t("carpoolAsPassenger") }}
        </v-btn>
      </v-card-actions>
    </v-card>

    <!-- confirm carpool dialog -->
    <!-- <v-dialog
      v-model="carpoolDialog"
      max-width="600"
    >
      <v-card>
        <v-card-title>
          {{ $t("confirmCarpoolTitle") }}
        </v-card-title>
        <v-card-text>
          {{ $t("confirmCarpool", { carpooler: lResult.carpooler.givenName }) }}
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="green darken-1"
            text
            @click="closeConfirmationDialog"
          >
            {{ $t("cancel") }}
          </v-btn>
          <v-btn
            color="primary"
            @click="carpool(carpoolRole)"
          >
            {{ $t("confirm") }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog> -->
  </div>
</template>

<script>
import moment from "moment";
import maxios from "@utils/maxios";
import {
  messages_en,
  messages_fr,
  messages_eu,
  messages_nl
} from "@translations/components/carpool/results/MatchingJourney/";

export default {
  components: {
  },
  i18n: {
    messages: {
      en: messages_en,
      nl: messages_nl,
      fr: messages_fr,
      eu: messages_eu
    }
  },
  props: {
    result: {
      type: Object,
      default: null
    },
    user: {
      type: Object,
      default: null
    }
  },
  data: function() {
    return {
      locale: localStorage.getItem("X-LOCALE"),
      lResult: this.result,
      contactLoading: false,
      carpoolLoading: false,
      primaryColor: this.$vuetify.theme.themes.light.primary,
      secondaryColor: this.$vuetify.theme.themes.light.secondary,
      bookingDialog: false,
    };
  },
  computed: {

    today() {
      return moment().toISOString();
    },

    computedTime() {
      if (this.result && this.result.time)
        return moment
          .utc(this.lResult.time)
          .format(this.$t("i18n.time.format.hourMinute"));
      return null;
    },
    computedDate() {
      if (this.result && this.result.date)
        return moment
          .utc(this.lResult.date)
          .format(this.$t("i18n.date.format.fullDate"));
      return null;
    },
  },
  mounted() {
    this.computedDate();
  },
  methods: {
    closeConfirmationDialog() {
      this.carpoolDialog = false;

      if (this.carpoolRoleSelected) {
        this.carpoolRoleSelected = null;
      }
    },
    booking(result) {
      this.$emit("booking", result);
    }
  }
};
</script>
<style lang="scss" scoped>
.toolbar {
  color: #fff;
}
</style>

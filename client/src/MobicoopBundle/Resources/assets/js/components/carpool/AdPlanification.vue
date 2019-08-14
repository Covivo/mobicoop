<template>
  <v-container fluid>
    <v-form>
      <!-- Punctual -->
      <!-- First row -->
      <v-row
        align="center"
        justify="center"
        dense
      >
        <v-col
          cols="6"
          offset="2"
        >
          <v-menu
            v-model="menuOutwardDate"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            full-width
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                :value="computedOutwardDateFormat"
                :label="$t('outwardDate.label')"
                readonly
                clearable
                v-on="on"
                @click:clear="clearOutwardDate"
              >
                <v-icon
                  slot="prepend"
                >
                  mdi-arrow-right-circle-outline
                </v-icon>
              </v-text-field>
            </template>
            <v-date-picker
              v-model="outwardDate"
              :locale="locale"
              no-title
              @input="menuOutwardDate = false"
              @change="change"
            />
          </v-menu>
        </v-col>

        <v-col
          cols="4"
        >
          <v-menu
            ref="menuOutwardTime"
            v-model="menuOutwardTime"
            :close-on-content-click="false"
            :return-value.sync="outwardTime"
            transition="scale-transition"
            offset-y
            full-width
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                v-model="outwardTime"
                :label="$t('outwardTime.label')"
                prepend-icon=""
                readonly
                v-on="on"
              />
            </template>
            <v-time-picker
              v-if="menuOutwardTime"
              v-model="outwardTime"
              no-title
              format="24hr"
              @click:minute="$refs.menuOutwardTime.save(outwardTime)"
              @change="change"
            />
          </v-menu>
        </v-col>
      </v-row>

      <!-- Second row -->
      <v-row
        align="center"
        justify="center"
        dense
      >
        <v-col
          cols="2"
        >
          <v-checkbox
            v-model="returnTrip"
            class="mt-0"
            :label="$t('returnTrip.label')"
            color="success"
            hide-details
            @change="change"
          />
        </v-col>

        <v-col
          cols="6"
        >
          <v-menu
            v-model="menuReturnDate"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            full-width
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                :value="computedReturnDateFormat"
                :label="$t('returnDate.label')"
                prepend-icon=""
                readonly
                :disabled="!returnTrip"
                v-on="on"
              >
                <v-icon
                  slot="prepend"
                >
                  mdi-arrow-left-circle-outline
                </v-icon>
              </v-text-field>
            </template>
            <v-date-picker
              v-model="returnDate"
              :locale="locale"
              no-title
              @input="menuReturnDate = false"
              @change="change"
            />
          </v-menu>
        </v-col>

        <v-col
          cols="4"
        >
          <v-menu
            ref="menuReturnTime"
            v-model="menuReturnTime"
            :close-on-content-click="false"
            :return-value.sync="returnTime"
            transition="scale-transition"
            offset-y
            full-width
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                v-model="returnTime"
                :label="$t('returnTime.label')"
                prepend-icon=""
                readonly
                :disabled="!returnTrip"
                v-on="on"
              />
            </template>
            <v-time-picker
              v-if="menuReturnTime"
              v-model="returnTime"
              no-title
              format="24hr"
              @click:minute="$refs.menuReturnTime.save(returnTime)"
              @change="change"
            />
          </v-menu>
        </v-col>
      </v-row>

    <!-- Regular -->
    </v-form>
  </v-container>
</template>

<script>
import moment from "moment";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/AdPlanification.json";
import TranslationsClient from "@clientTranslations/components/carpool/AdPlanification.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
  },
  props: {
    regular: {
      type: Boolean,
      default: false
    },
    initOutwardDate: String
  },
  data() {
    return {
      outwardDate: null,
      outwardTime: null,
      returnDate: null,
      returnTime: null,
      menuOutwardDate: false,
      menuOutwardTime: false,
      menuReturnDate: false,
      menuReturnTime: false,
      returnTrip: false,
      locale: this.$i18n.locale
    };
  },
  computed: {
    computedOutwardDateFormat() {
      moment.locale(this.locale);
      return this.outwardDate
        ? moment(this.outwardDate).format(this.$t("ui.i18n.date.format.fullDate"))
        : "";
    },
    computedReturnDateFormat() {
      moment.locale(this.locale);
      return this.returnDate
        ? moment(this.returnDate).format(this.$t("ui.i18n.date.format.fullDate"))
        : "";
    }
  },
  watch: {
    initOutwardDate() {
      this.outwardDate = this.initOutwardDate;
    }
  },
  methods: {
    change() {
      this.$emit("change", {
        outwardDate: this.outwardDate,
        outwardTime: this.outwardTime,
        returnDate: this.returnDate,
        returnTime: this.returnTime,
        returnTrip: this.returnTrip
      });
    },
    clearOutwardDate() {
      this.outwardDate = null;
      this.change();
    }
  }
};
</script>
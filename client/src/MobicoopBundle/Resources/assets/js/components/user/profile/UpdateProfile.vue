/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/
 
 <template>
 <div>
    <!--SnackBar-->
    <v-snackbar
      v-model="snackbar"
      :color="(this.errorUpdate)?'error':'success'"
      top
    >
      {{ (this.errorUpdate)?this.textSnackError:this.textSnackOk }}
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
      <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>
      <v-row
        justify-center
        text-center
      >
        <v-col class="text-center">
          <v-form
            ref="form"
            v-model="valid"
            lazy-validation
          >
            <!--Upload Avatar-->
            <v-row justify="center" v-if="user.images[0]">
              <v-col cols="3">
                <v-avatar
                  color="grey lighten-3"
                  size="225px"
                >
                  <img
                    :src="user['images'][0]['versions'][avatarVersion]"
                    alt="avatar"
                  >
                </v-avatar>
              </v-col>
              <v-col cols="1" justify-self="center" align-self="center">
                <v-icon @click="avatarDelete">
                  mdi-delete
                </v-icon>
              </v-col>
            </v-row>
            <v-row v-else class="justify-center">
              <v-col cols="12" class="text-center">
                <v-avatar
                  color="grey lighten-3"
                  size="225px"
                >
                  <img
                    :src="this.urlAltAvatar"
                    alt="avatar"
                  >
                </v-avatar>
              </v-col>
              <v-col cols="5" class="text-center">
                <v-file-input
                v-model="avatar"
                  :rules="avatarRules"
                  accept="image/png, image/jpeg, image/bmp"
                  :label="$t('avatar.label')"
                  prepend-icon="mdi-image"
                />
              </v-col>
            </v-row>

            <v-row class="text-left title font-weight-bold">
              <v-col>{{ $t('titles.personnalInfos') }}</v-col>
            </v-row>

              <!--Email-->
            <v-text-field
              v-model="user.email"
              :label="$t('models.user.email.label')"
              type="email"
              class="email"
            />

            <!--Telephone-->
            <v-text-field
              v-model="user.telephone"
              :label="$t('models.user.phone.label')"
              class="telephone"
            />

            <!--GivenName-->
            <v-text-field
              v-model="user.givenName"
              :label="$t('models.user.givenName.label')" 
              class="givenName"
            />

            <!--FamilyName-->
            <v-text-field
              v-model="user.familyName"
              :label="$t('models.user.familyName.label')" 
              class="familyName"
            />

            <!--Gender-->
            <v-select
              v-model="user.gender"
              :label="$t('models.user.gender.label')"
              :items="genders"
              item-text="gender"
              item-value="value"
            />

            <!--birthyear-->
            <v-select
              id="birthYear"
              v-model="user.birthYear"
              :items="years"
              :label="$t('models.user.birthYear.label')"
              class="birthYear"
            />

            <!--GeoComplete-->
            <GeoComplete
              :url="geoSearchUrl"
              :label="$t('models.user.homeTown.label')"
              :token="user ? user.geoToken : ''"
              :init-address="user.homeAddress ? user.homeAddress : null"
              :display-name-in-selected="false"
              @address-selected="homeAddressSelected"
            />
            
            <!--Save Button-->
            <v-btn
              class="button saveButton"
              color="primary"
              rounded
              :disabled="!valid"
              :loading="loading"
              type="button"
              :value="$t('ui.button.save')"
              @click="validate"
            >
              {{ $t('ui.button.save') }}
            </v-btn>
          </v-form>
        </v-col>
      </v-row>
      <v-row class="text-left title font-weight-bold">
        <v-col>{{ $t('titles.password') }}</v-col>
      </v-row>
      <v-row>
        <ChangePassword />
      </v-row>
 </div>
</template>

<script>
import axios from "axios";
import GeoComplete from "@js/components/utilities/GeoComplete";
import ChangePassword from "@components/user/profile/ChangePassword";
import { merge } from "lodash";
import Translations from "@translations/components/user/profile/UpdateProfile.json";
import TranslationsClient from "@clientTranslations/components/user/profile/UpdateProfile.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
  },
  components: {
    GeoComplete,
    ChangePassword
  },
  props: {
    avatarSize: {
      type: String,
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
    geoSearchUrl: {
      type: String,
      default: null
    },
    user: {
      type: Object,
      default: null
    },
    ageMin: {
      type: String,
      default: null
    },
    ageMax: {
      type: String,
      default: null
    },
  
  },
  data() {
    return {
      snackbar: false,
      textSnackOk: this.$t('snackBar.profileUpdated'),
      textSnackError: this.$t("snackBar.passwordUpdateError"),
      errorUpdate: false,  
      valid: true,
      errors: [],
      loading: false,
      gender: {
        value: this.user.gender
      },
      homeAddress: null,
      genders:[
        { value: 1, gender: this.$t('models.user.gender.values.female')},
        { value: 2, gender: this.$t('models.user.gender.values.male')},
        { value: 3, gender: this.$t('models.user.gender.values.other')},
      ],
      avatar: null,
      avatarRules: [
        v => !v || v.size < this.avatarSize || this.$t("avatar.size")+" (Max "+(this.avatarSize/1000000)+"MB)"
      ],
    };
  },
  computed : {
    years () {
      const currentYear = new Date().getFullYear();
      const ageMin = Number(this.ageMin);
      const ageMax = Number(this.ageMax);
      return Array.from({length: ageMax - ageMin}, (value, index) => (currentYear - ageMin) - index)
    },

  },
  methods: {
    homeAddressSelected(address){
      this.homeAddress = address;
    },
    validate () {
      if (this.$refs.form.validate()) {
        this.checkForm();
      }
    },
    
    checkForm () {
      this.loading = true;
      let updateUser = new FormData();
      updateUser.append("email", this.user.email);
      updateUser.append("familyName", this.user.familyName);
      updateUser.append("gender", this.user.gender);
      updateUser.append("givenName", this.user.givenName);
      updateUser.append("homeAddress", JSON.stringify(this.user.homeAddress));
      updateUser.append("telephone", this.user.telephone);
      updateUser.append("birthYear", this.user.birthYear);
      updateUser.append("avatar", this.avatar);

      axios 
        .post(this.$t('route.update'), updateUser, 
          {
          headers:{
            'content-type': 'multipart/form-data'
          }
        })
        .then(res => {
          this.errorUpdate = res.data.state;
          document.location.reload(true);
          this.snackbar = true;
      });
    },

    avatarDelete () {
      axios
        .get(this.$t('avatar.delete.route'))
        .then(res => {
          this.errorUpdate = res.data.state;
          document.location.reload(true);
        });
    }
  }
};
</script>
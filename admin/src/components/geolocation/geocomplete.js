import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import Downshift from 'downshift';
import { withStyles } from '@material-ui/core/styles';
import TextField from '@material-ui/core/TextField';
import Paper from '@material-ui/core/Paper';
import MenuItem from '@material-ui/core/MenuItem';
import { fetchUtils, FormDataConsumer } from 'react-admin';
import { useForm, useField } from 'react-final-form';
import pick from 'lodash.pick';
import useDebounce from '../../utils/useDebounce';

const queryString = require('query-string');

const fetchSuggestions = (input) => {
  if (!input) {
    return new Promise((resolve, reject) => resolve([]));
  }

  const apiUrl = process.env.REACT_APP_API + process.env.REACT_APP_GEOSEARCH_RESOURCE;
  const parameters = {
    q: `${input}`,
  };
  const urlWithParameters = `${apiUrl}?${queryString.stringify(parameters)}`;
  return fetchUtils
    .fetchJson(urlWithParameters)
    .then((response) => response.json)
    .catch((error) => {
      console.error(error);
      return [];
    });
};

const GeocompleteInput = ({ label, source, validate, classes, defaultValueText }) => {
  const form = useForm();
  const fieldName = source || 'address';
  const field = useField(fieldName);

  const [input, setInput] = useState('');
  const [suggestions, setSuggestions] = useState([]);
  const debouncedInput = useDebounce(input, 500);

  const formState = form.getState();
  const errorMessage = validate(input);
  const errorState = !!(formState.submitFailed && errorMessage);

  useEffect(() => {
    if (debouncedInput) {
      fetchSuggestions(debouncedInput).then((results) => {
        setSuggestions(
          results
            .filter((element) => element && element.displayLabel && element.displayLabel.length > 0)
            .slice(0, 10)
        );
      });
    } else {
      setSuggestions([]);
    }
  }, [debouncedInput]);

  const isSelected = (selectedItem, label) => (selectedItem || '').indexOf(label) > -1;

  return (
    <FormDataConsumer>
      {({ dispatch, formData, ...rest }) => (
        <div className={classes.root}>
          <Downshift
            initialInputValue={defaultValueText}
            onInputValueChange={(inputValue) => setInput(inputValue ? inputValue.trim() : '')}
            onSelect={(selectedItem, stateAndHelpers) => {
              const address = suggestions.find((element) => element.displayLabel === selectedItem);
              if (!address) {
                return;
              }

              field.input.onChange(
                pick(address, [
                  'streetAddress',
                  'postalCode',
                  'addressLocality',
                  'addressCountry',
                  'latitude',
                  'longitude',
                  'elevation',
                  'name',
                  'houseNumber',
                  'street',
                  'subLocality',
                  'localAdmin',
                  'county',
                  'macroCounty',
                  'region',
                  'macroRegion',
                  'countryCode',
                  'home',
                  'venue',
                ])
              );
            }}
          >
            {({ getInputProps, getItemProps, isOpen, selectedItem, highlightedIndex }) => (
              <div className={classes.container}>
                <TextField
                  label={label || 'Adresse'}
                  className={classes.input}
                  variant="filled"
                  required
                  source={`${fieldName}.county`}
                  error={errorState}
                  helperText={errorState && errorMessage}
                  InputProps={getInputProps({
                    placeholder: 'Entrer une adresse',
                  })}
                  fullWidth
                />
                {isOpen ? (
                  <Paper className={classes.paper} square>
                    {suggestions.map((suggestion, index) => (
                      <MenuItem
                        {...getItemProps({
                          item: suggestion.displayLabel,
                        })}
                        key={suggestion.displayLabel}
                        selected={highlightedIndex === index}
                        component="div"
                        style={{
                          fontWeight: isSelected(selectedItem, suggestion.displayLabel) ? 500 : 400,
                        }}
                      >
                        {suggestion.displayLabel.join(' ')}
                      </MenuItem>
                    ))}
                  </Paper>
                ) : null}
              </div>
            )}
          </Downshift>
        </div>
      )}
    </FormDataConsumer>
  );
};

GeocompleteInput.propTypes = {
  classes: PropTypes.object.isRequired,
  source: PropTypes.string.isRequired,
  label: PropTypes.string,
  validate: PropTypes.func,
};

GeocompleteInput.defaultProps = {
  label: '',
  validate: () => '',
};

const styles = (theme) => ({
  root: {
    flexGrow: 1,
  },
  container: {
    flexGrow: 1,
    position: 'relative',
  },
  paper: {
    position: 'absolute',
    zIndex: 9999,
    marginTop: theme.spacing(1),
    left: 0,
    right: 0,
  },
  chip: {
    margin: `${theme.spacing(0.5)}px ${theme.spacing(0.25)}px`,
  },
  inputRoot: {
    flexWrap: 'wrap',
  },
  divider: {
    height: theme.spacing(2),
  },
  input: {
    // width: '50%',   // Change this to style the autocomplete component
    flexGrow: 1,
  },
});

export default withStyles(styles)(GeocompleteInput);

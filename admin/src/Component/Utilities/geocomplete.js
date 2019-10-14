import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import Downshift from 'downshift';
import { withStyles } from '@material-ui/core/styles';
import TextField from '@material-ui/core/TextField';
import Paper from '@material-ui/core/Paper';
import MenuItem from '@material-ui/core/MenuItem';
import { fetchUtils, FormDataConsumer, REDUX_FORM_NAME } from 'react-admin';
import useDebounce from './useDebounce';
import { change } from 'redux-form';
import AwesomeDebouncePromise from 'awesome-debounce-promise';

const queryString = require('query-string');

const fetchSuggestions = input => {
    if (!input) {
        return new Promise((resolve, reject) => resolve([]));
    }

    const apiUrl = process.env.REACT_APP_API+process.env.REACT_APP_GEOSEARCH_RESOURCE;
    const parameters = {
        q: `${input}`,
    };
    const urlWithParameters = `${apiUrl}?${queryString.stringify(parameters)}`;
    return fetchUtils
        .fetchJson(urlWithParameters)
        .then(response => response.json)
        .catch(error => {
            console.error(error);
            return [];
        });
};

const GeocompleteInput = props => {
    const { classes } = props;

    const [input, setInput] = useState('');
    const [suggestions, setSuggestions] = useState([]);
    const debouncedInput = useDebounce(input, 500);

    useEffect(() => {
        if (debouncedInput) {
            fetchSuggestions(debouncedInput).then(results => {
                setSuggestions(
                    results
                        .filter(
                            element =>
                                element &&
                                element.displayLabel &&
                                element.displayLabel.trim().length > 0,
                        )
                        .slice(0, 5),
                );
            });
        } else {
            setSuggestions([]);
        }
    }, [debouncedInput]);

    const isSelected = (selectedItem, label) => (selectedItem || '').indexOf(label) > -1;

    return (
        <FormDataConsumer>
            {({ dispatch, ...rest }) => (
                <div className={classes.root}>
                    <Downshift
                        onInputValueChange={(inputValue, stateAndHelpers) =>
                            setInput(inputValue ? inputValue.trim() : '')
                        }
                        onSelect={(selectedItem, stateAndHelpers) => {
                            const address = suggestions.find(
                                element => element.displayLabel === selectedItem,
                            );
                            if (address) {
                                // dispatch here the fields you want to store in the react-admin model
                                dispatch(change(REDUX_FORM_NAME, 'address.streetAddress', address.streetAddress));
                                dispatch(change(REDUX_FORM_NAME, 'address.postalCode', address.postalCode));
                                dispatch(change(REDUX_FORM_NAME, 'address.addressLocality', address.addressLocality));
                                dispatch(change(REDUX_FORM_NAME, 'address.addressCountry', address.addressCountry));
                                dispatch(change(REDUX_FORM_NAME, 'address.latitude', address.latitude));
                                dispatch(change(REDUX_FORM_NAME, 'address.longitude', address.longitude));
                                dispatch(change(REDUX_FORM_NAME, 'address.elevation', address.elevation));
                                dispatch(change(REDUX_FORM_NAME, 'address.name', address.name));
                                dispatch(change(REDUX_FORM_NAME, 'address.houseNumber', address.houseNumber));
                                dispatch(change(REDUX_FORM_NAME, 'address.street', address.street));
                                dispatch(change(REDUX_FORM_NAME, 'address.subLocality', address.subLocality));
                                dispatch(change(REDUX_FORM_NAME, 'address.localAdmin', address.localAdmin));
                                dispatch(change(REDUX_FORM_NAME, 'address.county', address.county));
                                dispatch(change(REDUX_FORM_NAME, 'address.macroCounty', address.macroCounty));
                                dispatch(change(REDUX_FORM_NAME, 'address.region', address.region));
                                dispatch(change(REDUX_FORM_NAME, 'address.macroRegion', address.macroRegion));
                                dispatch(change(REDUX_FORM_NAME, 'address.countryCode', address.countryCode));
                                dispatch(change(REDUX_FORM_NAME, 'address.home', address.home));
                                dispatch(change(REDUX_FORM_NAME, 'address.venue', address.venue));
                            }
                        }}
                    >
                        {({
                            getInputProps,
                            getItemProps,
                            isOpen,
                            selectedItem,
                            highlightedIndex,
                        }) => (
                            <div className={classes.container}>
                                <TextField
                                    label="Adresse"
                                    className={classes.input}
                                    InputProps={{
                                        ...getInputProps({
                                            placeholder: 'Entrer une adresse',

                                        }),
                                    }}
                                    fullWidth={true}
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
                                                    fontWeight: isSelected(
                                                        selectedItem,
                                                        suggestion.displayLabel,
                                                    )
                                                        ? 500
                                                        : 400,
                                                }}
                                            >
                                                {suggestion.displayLabel}
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

const debouncedFetchSuggestions = AwesomeDebouncePromise(fetchSuggestions, 500);
class GeocompleteInputWithoutHook extends React.Component {
    state = {
        input: '',
        suggestions: [],
    };

    handleInputChange = inputValue => {
        this.setState({ input: inputValue ? inputValue.trim() : '' });

        debouncedFetchSuggestions(inputValue).then(results => {
            this.setState({
                suggestions: results
                    .filter(
                        element =>
                            element &&
                            element.displayLabel[0] &&
                            element.displayLabel[0].trim().length > 0
                    )
                    .slice(0, 5),
            });
        });
    };

    render() {
        const { classes } = this.props;

        const isSelected = (selectedItem, label) => (selectedItem || '').indexOf(label) > -1;
        return (
            <FormDataConsumer>
                {({ dispatch, ...rest }) => (
                    <div className={classes.root}>
                        <Downshift
                            onInputValueChange={(inputValue, stateAndHelpers) => {
                                this.handleInputChange(inputValue ? inputValue.trim() : '');
                            }}
                            onSelect={(selectedItem, stateAndHelpers) => {
                                const address = this.state.suggestions.find(
                                    element => `${element.displayLabel[0]} - ${element.displayLabel[1]}` === selectedItem,
                                );
                                if (address) {
                                    // console.log(this.props);
                                    // dispatch here the fields you want to store in the react-admin model
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.streetAddress', address.streetAddress));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.postalCode', address.postalCode));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.addressLocality', address.addressLocality));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.addressCountry', address.addressCountry));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.latitude', address.latitude));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.longitude', address.longitude));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.elevation', address.elevation));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.name', address.name));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.houseNumber', address.houseNumber));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.street', address.street));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.subLocality', address.subLocality));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.localAdmin', address.localAdmin));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.county', address.county));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.macroCounty', address.macroCounty));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.region', address.region));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.macroRegion', address.macroRegion));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.countryCode', address.countryCode));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.home', address.home));
                                    dispatch(change(REDUX_FORM_NAME, this.props.source+'.venue', address.venue));
                                }
                            }}
                        >
                            {({
                                getInputProps,
                                getItemProps,
                                isOpen,
                                selectedItem,
                                highlightedIndex,
                            }) => (
                                <div className={classes.container}>
                                    <TextField
                                        label={this.props.label}
                                        className={classes.input}
                                        value={this.props.value}
                                        isrequired={this.props.isRequired}
                                        InputProps={{
                                            ...getInputProps({
                                                placeholder: this.props.placeholder
                                            }),
                                        }}
                                        fullWidth={true}
                                    />

                                    {isOpen ? (
                                        <Paper className={classes.paper} square>
                                            {this.state.suggestions.map((suggestion, index) => (
                                                <MenuItem
                                                    {...getItemProps({
                                                        item: `${suggestion.displayLabel[0]} - ${suggestion.displayLabel[1]}`,
                                                    })}
                                                    key={`${suggestion.displayLabel[0]} - ${suggestion.displayLabel[1]}`}
                                                    selected={highlightedIndex === index}
                                                    component="div"
                                                    style={{
                                                        fontWeight: isSelected(
                                                            selectedItem,
                                                            `${suggestion.displayLabel[0]} - ${suggestion.displayLabel[1]}`,
                                                        )
                                                            ? 500
                                                            : 400,
                                                    }}
                                                >
                                                    {`${suggestion.displayLabel[0]} - ${suggestion.displayLabel[1]}`}
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
    }
}

GeocompleteInput.propTypes = {
    classes: PropTypes.object.isRequired,
};

GeocompleteInputWithoutHook.propTypes = {
    classes: PropTypes.object.isRequired,
};

const styles = theme => ({
    root: {
        flexGrow: 1
    },
    container: {
        flexGrow: 1,
        position: 'relative',
    },
    paper: {
        position: 'absolute',
        zIndex: 9999,
        marginTop: theme.spacing.unit,
        left: 0,
        right: 0,
    },
    chip: {
        margin: `${theme.spacing.unit / 2}px ${theme.spacing.unit / 4}px`,
    },
    inputRoot: {
        flexWrap: 'wrap',
    },
    divider: {
        height: theme.spacing.unit * 2,
    },
    input: { width: 256, flexGrow:1 },
});

export default withStyles(styles)(GeocompleteInputWithoutHook);
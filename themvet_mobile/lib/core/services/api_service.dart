import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/api_constants.dart';

class ApiService {
  static String? _token;
  
  static Future<String?> getToken() async {
    if (_token != null) return _token;
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    return _token;
  }
  
  static Future<void> setToken(String token) async {
    _token = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }
  
  static Future<void> clearToken() async {
    _token = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }
  
  static Map<String, String> _getHeaders({bool withAuth = true}) {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (withAuth && _token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }
    return headers;
  }
  
  static Future<Map<String, dynamic>> get(String endpoint, {Map<String, String>? queryParams}) async {
    String url = '${ApiConstants.baseUrl}$endpoint';
    if (queryParams != null && queryParams.isNotEmpty) {
      final queryString = queryParams.entries
          .map((e) => '${e.key}=${Uri.encodeComponent(e.value)}')
          .join('&');
      url = '$url?$queryString';
    }
    
    final response = await http.get(
      Uri.parse(url),
      headers: _getHeaders(),
    );
    
    return _handleResponse(response);
  }
  
  static Future<Map<String, dynamic>> post(String endpoint, {Map<String, dynamic>? body, bool withAuth = true}) async {
    final response = await http.post(
      Uri.parse('${ApiConstants.baseUrl}$endpoint'),
      headers: _getHeaders(withAuth: withAuth),
      body: body != null ? jsonEncode(body) : null,
    );
    
    return _handleResponse(response);
  }
  
  static Future<Map<String, dynamic>> put(String endpoint, {Map<String, dynamic>? body}) async {
    final response = await http.put(
      Uri.parse('${ApiConstants.baseUrl}$endpoint'),
      headers: _getHeaders(),
      body: body != null ? jsonEncode(body) : null,
    );
    
    return _handleResponse(response);
  }
  
  static Map<String, dynamic> _handleResponse(http.Response response) {
    Map<String, dynamic> body;
    try {
      body = jsonDecode(response.body) as Map<String, dynamic>;
    } catch (_) {
      throw Exception('Server error (${response.statusCode}). Please try again.');
    }
    
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return body;
    }
    throw Exception(body['message'] ?? 'Request failed (${response.statusCode})');
  }
}

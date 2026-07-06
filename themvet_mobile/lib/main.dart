import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'theme/app_theme.dart';
import 'routes/app_routes.dart';
import 'core/services/auth_provider.dart';

void main() {
  runApp(const ThemVetApp());
}

class ThemVetApp extends StatelessWidget {
  const ThemVetApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => AuthProvider()..init(),
      child: Consumer<AuthProvider>(
        builder: (context, auth, _) {
          return MaterialApp(
            title: 'ThemVet',
            theme: AppTheme.lightTheme,
            initialRoute: auth.isAuthenticated ? AppRoutes.home : AppRoutes.login,
            routes: AppRoutes.routes,
            debugShowCheckedModeBanner: false,
          );
        },
      ),
    );
  }
}
